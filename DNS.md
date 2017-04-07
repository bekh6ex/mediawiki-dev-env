`docker network create mediawiki`

`docker run -d --restart=always --name=elama-dns -p 127.0.1.14:53:53/tcp -p 127.0.1.14:53:53/udp --net=elama --cap-add=NET_ADMIN andyshinn/dnsmasq:2.75 --no-negcache --cache-size=0 --max-cache-ttl=0`

###Отключить mDNS
`sudo nano /etc/nsswitch.conf`

Заменить `hosts` на `hosts: files dns`

`sudo service network-manager restart`

###Добавить DNS сервер в resolv.conf
Если не установлен resolvconf:
`sudo apt-get install resolvconf`

`sudo nano /etc/resolvconf/resolv.conf.d/head`

`nameserver 127.0.1.14`

`sudo resolvconf -u`

###Проверка

**Важно:** при миграции со старой схемы нужно удалить `app/config/parameters.yml`

`build/run-dev.sh`

`ping elama.local` -> IP должен быть докеровский: `172.x.x.x`

**PS:** Если видим IP 10.x.x.x , то нужно закоментить строки по доменам *elama.local в файле /etc/hosts


###**P.P.S:** docker`овская dns-ка сейчас работает слегка не правильно - кеширует записи. Для верной работы нужно её удалить `docker rm -f elama-dns` и запустить с выключением кеша:
`docker run -d --restart=always --name=elama-dns -p 127.0.1.14:53:53/tcp -p 127.0.1.14:53:53/udp --net=elama --cap-add=NET_ADMIN andyshinn/dnsmasq:2.75 --no-negcache --cache-size=0 --max-cache-ttl=0`
(C) Схоронено из slack