# Cotxes Redis (PHP + Docker)

Aplicació simple per gestionar cotxes amb PHP i Redis Cloud.

## Ús

```bash
# en PWD
apk add git https://github.com/iesbillenguatges/redis-php.git
git clone 
docker build -t cotxes-redis .
docker run -d -p 8080:80 cotxes-redis
```

Després obri al navegador: http://localhost:8080
