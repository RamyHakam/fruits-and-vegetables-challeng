# 🍎🥕 Fruits and Vegetables

# Produce Service

A Symfony project  implementing Domain-Driven Design (DDD) for managing produce items (fruits and vegetables). The **Domain** layer encapsulates all business logic for products, while other layers (Application, Infrastructure, UI) provide ports, adapters, and interfaces.

---

## 📦 Prerequisites

* Docker & Docker Compose
* PHP 8.x & Composer (inside container)

---

## 🚀 Getting Started

Use the provided Makefile to build, start containers, import data, flush Redis, and run tests.

> **Usage**:
>
> 1. `make up` to build and start services.
> 2. `make data-import` to load seed data.
> 3. `make flush-redis` to clear cache.
> 4. `make test` to run all unit tests.

---

## 🔌 API Endpoints

Use the provided Postman collection

* **GET** `/api/produce/{type}/{id}` — Retrieve produce by  type , Id , and optional unit conversion (grams/kilograms)
* **GET** `/api/produce/{type}` — Retrieve produce by type with optional filters and optional unit conversion (grams/kilograms).
* **POST** `/api/produce/{type}` — Add list of produces to the collection.
* **DELETE** `/api/produce/{type}/{id}` — Remove a produce by ID.

---

## 🏗️ Architectural Highlights

* **DDD**: Clear separation between Domain, Application, Infrastructure, and UI layers.
* **Clean Architecture**: Ports & Adapters keep business logic framework-agnostic.
* **KISS, DRY, YAGNI, SOLID**: Emphasis on simplicity, reusability, and maintainability.
* **Redis Caching**: Fast access to current lowest prices.
* **FrankenPHP + Caddy**: Single-runtime HTTP server, no PHP-FPM or Nginx required.

*Happy coding!* 🚀


## 🎯 Goal
We want to build a service which will take a `request.json` and:
* Process the file and create two separate collections for `Fruits` and `Vegetables`
* Each collection has methods like `add()`, `remove()`, `list()`;
* Units have to be stored as grams;
* Store the collections in a storage engine of your choice. (e.g. Database, In-memory)
* Provide an API endpoint to query the collections. As a bonus, this endpoint can accept filters to be applied to the returning collection.
* Provide another API endpoint to add new items to the collections (i.e., your storage engine).
* As a bonus you might:
  * consider giving an option to decide which units are returned (kilograms/grams);
  * how to implement `search()` method collections;
  * use latest version of Symfony's to embed your logic 

### ✔️ How can I check if my code is working?
You have two ways of moving on:
* You call the Service from PHPUnit test like it's done in dummy test (just run `bin/phpunit` from the console)

or

* You create a Controller which will be calling the service with a json payload

## 💡 Hints before you start working on it
* Keep KISS, DRY, YAGNI, SOLID principles in mind
* We value a clean domain model, without unnecessary code duplication or complexity
* Think about how you will handle input validation
* Follow generally-accepted good practices, such as no logic in controllers, information hiding (see the first hint).
* Timebox your work - we expect that you would spend between 3 and 4 hours.
* Your code should be tested
* We don't care how you handle data persistence, no bonus points for having a complex method

## When you are finished
* Please upload your code to a public git repository (i.e. GitHub, Gitlab)

## 🐳 Docker image
Optional. Just here if you want to run it isolated.

### 📥 Pulling image
```bash
docker pull tturkowski/fruits-and-vegetables
```

### 🧱 Building image
```bash
docker build -t tturkowski/fruits-and-vegetables -f docker/Dockerfile .
```

### 🏃‍♂️ Running container
```bash
docker run -it -w/app -v$(pwd):/app tturkowski/fruits-and-vegetables sh 
```

### 🛂 Running tests
```bash
docker run -it -w/app -v$(pwd):/app tturkowski/fruits-and-vegetables bin/phpunit
```

### ⌨️ Run development server
```bash
docker run -it -w/app -v$(pwd):/app -p8080:8080 tturkowski/fruits-and-vegetables php -S 0.0.0.0:8080 -t /app/public
# Open http://127.0.0.1:8080 in your browser
```
