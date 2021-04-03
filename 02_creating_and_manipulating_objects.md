## Introduction value objects

Ask yourself following question "Is any string, int, float allowed to be passed?". 
If answer is no, consider creating new value object.

Values that are always passed together could be grouped together.

Consider following example:
```php
final class Product
{
    public function setPrice(
        Amount amount,
        Currency currency
    ): void {
// ... }
}
```

Amount and current is always passed to product. Consider extracting a class
to represent it in your code.

```php
final class Price
{
    public function __construct(Amount $amount, Curreny $curreny)
    {
        //
    } 
}
final class Product
{
    public function setPrice(Price $price): void 
    {
        // ... }
    }
```

- Don't inject dependencies for objects that are not services
- Use named constructor when there are multiple ways of creating object (from string, int etc.)
- Reuse validation logic by passing it in private constructor

## Data transfer object
- Used to carry data from outside world
- Doesn't have any constraints
- Needs to be validated in bulk

```php
class ProductResponse
{
    public string $id;
    public string $name;
    
    public static function fromRawData(array $data)
    {
        $product = new ProductResponse();
        $product->id = $data['id']; 
     
        $product->name = $data['name']; 
    }
    
    public function validate(): array
    {
        $errors = [];
        
        if ($this->id === '') {
        
        }
        
        if ($this->name === '') {
        
        }
    }
}
```

- default to immutable and use mutable if needed
- use imperative methods names for methods that mutates states (moveLeft)
- consider using domain concepts for named constructors in value objects (e.g. toTheLeft instead of withX)

Consider comparing value objects directly instead of exposing getters.
```php
public function it_can_move_to_the_left(): void
{
    $position = new Position(10, 20);
    $nextPosition = position.toTheLeft(4); 
    assertEquals(new Position(6, 20), nextPosition);
}
```

## Entities 

### Entities should also avoid invalid state

```php
final class TotalDistanceTraveled
{
    private int $totalDistance = 0;
    
    public function add(int $distance): TotalDistanceTraveled
    {
        Assert.greatherThanZero($distance);
        
        //
    }
}
```

### Entities are mutable and should record events about mutations

```php
final class Booking
{
    public function new(string $reference, \DateTimeInterface $start)
    {
        //do some stuff
        
        $this->record(new BookingCreatedEvent($booking));
    }
}
```

### Creating methods template

In your application you have a bunch of differnet objects each do something. Object is exposing its behaviour
via methods. We can have common template for creating object methods.

```
[scope] function methodName (...arguments): void|[return-type]
{
    [precondition checks]
        // Make sure all data is expected
        Assert.isArrayOfStrings(argument)
        
        // Consider extracting objects for data that contain some particular logic
        // eg. int $age  $age > 0 && $age < 150  -> HumanAge 
        // replace primitive with object
        
        // Usually invalid argument exceptions
    [/precondition checks]
    
    [faliure secnarios]
        // Runtime exceptions, e.g. record id not found
    [/faliure secnarios]
    
    [happy path]
        Whatever this method actually does, should be small
    [/happy path]
    
    [post condition checks]
        // verify that your results from hapy path are correct if any
    [/post condition checks]
    
    [return or void] 
}
```

### Extracting custom exceptions

Extracting custom exception is useful tehnice to provide more context and make your code cleaner.
Avoid having `Exception` suffix, you can use meaningful name for that. For example CouldNotFound::for($id);




