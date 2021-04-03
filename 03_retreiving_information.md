## Retrieving information

### Define abstraction for queries that cross system boundaries

For example when you need to fetch ExchangeRate from 3rd party API.

```php
interface ExchangeRate
{
    public function getRate(Curreny $from, Currency $to): Rate
}

class YahooFinanceExchangeRate
{
    private HttpClientInterface $http;
    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }
    
    public function getRate(Curreny $from, Currency $to): Rate
    {
        return $this->http->get('/rates', $from, $to);
    }
}
```

Other examples for crossing system boundaries:
- getting current time (uses underlying system clock)
- filesystem access
  
### Encapsulate objects

Objects should provide only information that is needed for consumer. Don't automatically expose instead first
consider how we can solve this without exposing data outside.

```php
class ShoppingCart
{
    private array $items;
    
    public function __construct(array $items) 
    {
        $this->items = $items;
    }
    
    public function getItems(): array<Item>
    {
        return $this->items;
    }
}

$itemsCount = count($shopingCart->getItems());

class ShoppingCart
{
    private array $items;
    
    public function __construct(array $items) 
    {
        $this->items = $items;
    }
    
    public function itemsCount(): int
    {
        return count($this->items);
    }
}

// Avoids exposing whole internal state, instead provides information based on data it has inside.
// In other words exposes behaviour instead of state.
$itemsCount = $shopingCart->itemsCount();
```

### Separate service and query sometimes

Imagine you have UserController and method that needs to update User. We want to return updated user on success.
Usually this is done in a way that service returns object.

```php
class UserController
{
    public function update(UpdateUserRequest $request): 
    {
        return new JsonResponse($this->userService->update($request));
    }
}
```

This could be solved with separate write and read operation.
```php
class UserController
{
    public function update(UpdateUserRequest $request): 
    {
        $id = IdGenerator::new();
        $this->userService->update($id, $request)
        
        $user = $this->users->get($id);
        
        return new JsonResponse($user);
    }
}
```

### Think about return types

Return types should ideally be only single type. Returning null is fine but first consider alternative such as
empty array, null object pattern, default value (false, etc.) or even throwing an exception.

```php
$repository->getProduct($id); // We expect product is found and throw exception othervise
$repository->findProduct($id) // We are finding something, exception is not expected
```
