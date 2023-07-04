# INOWAS DSS Backend API

A simple api to store relevant data

## Local Dev Environment

### Install

1. **Clone the repository**
```
git clone ...
```
2. **Adapt the configuration**
```
cp .env.dist .env
```
Now you can change all relevant parameter in the .env-file.

3. **Build local dev environment**
```
make dev-install
```
4. **Start local dev environment**
```
make dev-start
```
5. **Stop local dev environment**
```
make dev-stop
```

### Use the local dev environment

#### User logins

Users are created from file users.dist.json.

#### Debugging with xdebug




