<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\CurlMultiHandler;
use function GuzzleHttp\Promise\all;
use GuzzleHttp\Promise\PromiseInterface;

require_once __DIR__ . '/vendor/autoload.php';

class Application {
	const MAX_PENDING_REQUESTS = 30;

	/**
	 * Application constructor.
	 */
	public function __construct() {
		$this->handler = new CurlMultiHandler();
		$this->client = new GuzzleHttp\Client(
			[
				'handler' => $this->handler
			]
		);
	}

	public function main() {

		/**
		 * Dates checked: 2018-08-01 - 2018-08-07
		 */

		/**
		 * Range: Q1 - Q101
		 * Total views: 21607
		 * Total pages: 89
		 * Views per page per week: 243
		 */

		/**
		 * Range: Q1 - Q1000
		 * Total views: 81061
		 * Total pages: 975
		 * Views per page per week: 83
		 */

		/**
		 * Range: Q5000 - Q6000
		 * Total views: 21153
		 * Total pages: 933
		 * Views per page per week: 23
		 */

		/**
		 * Range: Q10000 - Q11000
		 * Total views: 9291
		 * Total pages: 1001
		 * Views per page per week: 9
		 */

		/**
		 * Range: Q100000 - Q101000
		 * Total views: 2555
		 * Total pages: 1001
		 * Views per page per week: 3
		 */

		/**
		 * Range: Q1000000 - Q1001000
		 * Total views: 1790
		 * Total pages: 576
		 * Views per page per week: 3
		 */

		/**
		 * Range: Q10000000 - Q10001000
		 * Total views: 227
		 * Total pages: 117
		 * Views per page per week: 2
		 */

		/**
		 * Range: Q30000000 - Q30001000
		 * Total views: 311
		 * Total pages: 162
		 * Views per page per week: 2
		 */

		/**
		 * Range: Q50000000 - Q50001000
		 * Total views: 44
		 * Total pages: 11
		 * Views per page per week: 4
		 */

		$min = 1000000;
		$max = $min + 1000;
		$stats = $this->getStats( range( $min, $max ) );

		$stats = all( $stats )->wait();

		$stats = array_filter(
			$stats,
			function ( $v ) {
				return $v !== null;
			}
		);

		$this->log( "Range: Q{$min} - Q{$max}");
		$this->log( "Total views: " . array_sum( $stats ) );
		$this->log( "Total pages: " . count( $stats ) );
		$this->log( "Views per page per week: " . round( array_sum( $stats ) / count( $stats ) ) );
	}

	public function getStats( array $entityIds ) {

		$pendingRequests = [];

		do {
			$entityId = array_shift( $entityIds );

			$result = $this->getEntityViews( $entityId );
			$pendingRequests[] = $result;

			yield $result;

			while ( count( $pendingRequests ) >= self::MAX_PENDING_REQUESTS ) {
				$pendingRequests = array_filter(
					$pendingRequests,
					function ( PromiseInterface $request ) {
						return $request->getState() === PromiseInterface::PENDING;
					}
				);
				$this->handler->tick();
			}

		} while ( !empty( $entityIds ) );

	}

	/**
	 * @return string
	 */
	private function getUrl( int $entityId ) {
		return "https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/wikidata.org/all-access/all-agents/Q{$entityId}/daily/20180801/20180807";
	}

	/**
	 * @param $entityId
	 * @return \GuzzleHttp\Promise\PromiseInterface
	 */
	private function getEntityViews( $entityId ) {
		$url = $this->getUrl( $entityId );

		// Send an asynchronous request.
		$request = new \GuzzleHttp\Psr7\Request( 'GET', $url );
		$this->log( "Sending request for Q{$entityId}" );

		$promise = $this->client->sendAsync( $request )
			->then(
				function ( $response ) use ( $entityId ) {
					$this->log( "Got response for Q{$entityId}" );

					if ($response->getStatusCode() === 404) {
						$this->log( "Not found Q{$entityId}" );
						return null;
					}

					$body = $response->getBody();
					$data = json_decode( $body );
					if ( !$data ) {
						throw new \Exception( 'Invalid format: ' . $body );
					}

					$views = array_map(
						function ( $item ) {
							return $item->views;
						},
						$data->items ?? []
					);
					return array_sum( $views ?? 0 );
				},
				function ( $e ) use ( $entityId ) {

					if (
						$e instanceof ClientException
						&& $e->hasResponse()
						&& $e->getResponse()->getStatusCode() === 404
					) {
						$this->log( "Not found Q{$entityId}" );
						return null;
					}

					$this->log( "Error with Q{$entityId}" );

					throw $e;
				}
			);

		return $promise;
	}

	private function log( $string ) {
		file_put_contents( 'php://stdout', $string . PHP_EOL );
	}

}

( new Application() )->main();
