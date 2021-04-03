## Types of objects

In an application there will typically be two types of objects
- Service objects that perform a task
- Objects that hold some data 
and optionally expose some behaviour manipulating or retrieving that data

## Inject dependencies and configuration values as constructor arguments
Services usually need other services to do the work. Those are called dependencies and they
should be injected as constructor arguments because service can't function without them.
Doing it in described way allows us to use service after it was instantiated without requiring additional setup.

Always try to inject what you need not where you can get it from. This helps to maintain services
responsibility, and it clearly defines its dependencies and purpose.

```php
class FileLogger implements Logger
{
    private Formatter $formatter;
    
    private string $filePath;

    public function __construct(Formatter $formmater, string $filePath)
    {
        $this->formatter = $formatter;
        $this->filePath = $filePath;    
    }
   
    public function log(string $message): void
    {
        $formattedMessage = $this->formatter->format($message);
        
        //log
    }
}
```

### Keep together configuration values that belong together

Configuration values that always go together should ideally be grouped together.

```php
// Username and password belong together.
final class ApiClient
{
    private string $userName;
    private string $password;
    
    public function __construct(string $userName, string $password)
    {
        $this->userName = $userName;
        $this->password = $password;    
    }
}

//Instead we can inject Credentials class
class Credentials
{
    private string $userName;
    private string $password;

    public function __construct(string $userName, string $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    public function userName(): string
    {
        return $this->userName;
    }

    public function password(): string
    {
        return $this->password;
    }
}

final class ApiClient
{
    private Credentials $credentials;
    
    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }
}
```

#### What if I need service, and the service I retrieve from it?

In this usually case the service you retrieve from could be bundled together.
Classic example for this is symfony EntityManager and Repository class.

```php
// We use entity manager to get UserRepository which is our main dependency.
$user = $this->entityManager->getRepository(User:class)->find($userId);
$this->entityManager->persit($user);
$this->entityManager->flush();

// Instead we can do this
$user = $this->userRepository->find($userId);
$user->updateCredentials($credentials);
$this->userRepository->save($user);
```

### All constructor arguments should be required

*Always avoid optional dependencies*. There is probably no such a thing as optional argument only poorly written services. 

```php
final class BankStatmentImporter
{
    private LoggerInterface? $logger;

    public function __construct(?LoggerInterface $logger = null) 
    {
        $this->logger = $logger;
    }
    
    public function __invoke()
    {
        // Now each time we need to log something we first need to check if logger is provided
        if ($this->logger instance LoggerInterface) {
            $this->logger->log('something');
        }       
    }
}
```

Instead, we can make logger required argument and if we don't need logging we can implement `NullLogger` or similar
`Null object` pattern. 

### Services should be immutable

Avoid using setter injections since they make services mutable. General rule is to inject all dependencies in the
constructor. Using setter injection makes things harder to reason about and complicates testing also services end up 
in incomplete state. 

Having object dependencies is also a first step towards making the behaviour of the service reconfigurable without 
touching its code.

```php
final class BankStatmentImporter
{
    private LoggerInterface? $logger;

    public function setLogger(LoggerIntergace $logger): void
    {
        $this->logger = $logger
    }
}
```


## Hidden decencies

### Turn static dependencies into object dependencies

Static dependencies are harder to follow because they are injected as constructor arguments. Ideally we want to
inject static dependencies in constructor as well.

### Turn complicated functions into decencies

Sometimes dependencies are hidden because they are functions not objects. These functions are often part of the
standard library of the language such as `json_encode`. Those classes are also dependencies and if they require some 
boilerplate code to function as you expect it then it is better to wrap it into a class.

For example, we can wrap `json_encode` function
```php
final class JsonEncoder
{
    public function encode(array $data): string
    {   
        try {
            json_encode($data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        } catch (RuntimeException $exception) {
            throw new RuntimeException('Failed to encode data', var_export($data));
        }          
    }
}
```

### Make system calls explicit

Function calls that reach out to the world outside are also considered implicit dependencies.
Examples are a `DateTime` class and functions like `time()` or `file_get_contents()`.

```php
interface Clock 
{
    public function getCurrentTime(): DateTimeImmutable;
}

final class SystemClock
{
    public function getCurrentTime(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }   
}

final class MeetupRepository
{
    private Clock $clock;
    
    public function __construct(Clock $clock) 
    {
        $this->clock = $clock;
    }
    
    /**
     * @return Meetup[]
    */
    public function findUpcomingMeetups(Area $area): array 
    {
        // We can now use our clock instead of directly instantiating DateTime class and depending on
        // system calls.
        $now = $this->clock->getCurrentTime();
    }
}
```

### Constructor injection vs argument injection

There are few questions that will guide your decision of injection type

1. Could I run this service in a batch without requiring it to be instantiated over and over again?
2. If memory is not wiped after each request can this service be used for subsequent requests or in needs to be reused.

Services should be used to perform same job over and over again without need to re-instantiate them again. If 
this statement is not satisfied service should be refactored.


```php
//BAD 
class EntityManager
{
    public function __construct(
        private object $entity
    ) {}
    
    public function save(): void
    {
       $this->connection->save(this->entity)
    }
}

$em = new EntityManager(obj1)
$em->save();
``

// For each other object it should be instantiated again which is wrong.

// GOOD
```php
class EntityManager
{
    public function save(object $entity): void
    {
      $this->connection->save(this->entity)
    }
}

$em = new EntityManager()
$em->save($obj1);
$em->save($obj2);
```

## Do nothing inside a constructor

Avoid doing things inside constructor because it may modify behaviour even if class/service if actually
methods of class services are never used.

## Throw an exception when argument is invalid

```php
class Alerting
{
    private int $level;
    
    public function __construct(int $level): void
    {
        if ($level < 0 || $level > 10) {
            throw new InvalidArgumentException('Level shoud be between 0 and 10.');
        }
        
        $this->level = $level;
    }
}

$em = new EntityManager()
$em->save($obj1);
$em->save($obj2);
```

## Summary
- Services should be created in one go.
- All dependencies should be injected in the constructor and should be validated
- Contextual information should be passed as an argument
- After instantiation service should be immutable (e.g. can't change behaviour by calling it's methods like setLogger())























