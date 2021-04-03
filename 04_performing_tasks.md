Besides, retrieving information and manipulating object data we also use objects to perform a tasks.
This kind of objects is called "Command" objects. There are several guidelines for creating such objects.

## Command methods have imperative form

Command methods do some job and to express intent they should be named in imperative form
- sendEmail()
- calculateProfit()

They should return void type whenever possible. Consider separate command and query (CQRS principle).

## Use events to perform a secondary task

Most of the time commands multiple tasks. In order to separate those dispatching event is used.

```php
class RegisterUser
{
    public function __invoke(string $username, string $password): void
    {
        $user = User::fromCredentials($username, $password);
    
        // if something goes wrong throw exception
        $has = $this->users->has($user);
        
        if ($has) {
            throw new UserAlreadyExists($user);
        }
        
        // Main job/responsibility of this class
        $this->users->save($user);
        
        // Secondary tasks
        // If those are taken into account our class should be named
        // RegisterUserAndSendEmailAndCreateInitialFolder 
        // That is indicator that this class is doing too much
        $this->mails->send(new WelcomeEmail($user));
        $this->storage->createForUser($user);
        
        // Insead of secondary tasks it is better to dispatch event and do it in a separate service
        $this->events->dispatch(new UserRegistered($user));
    }
}
```

## Services should be immutable

Services should be immutable, and they can be instantiated and called as many times as needed without introducing side effects.

```php
class Mailer 
{
    private array $sendTo = [];
    
    public function sendConfirmationEmail(Email $recipient): void
    {
        if (in_array($recipient, $this->sentTo, true)) {
            return;
        }
    }
}

$mailer = new Mailer();
$mailer->sendConfirmationEmail(new Email('jurica.separovic@gmail.com')); // Sends email
$mailer->sendConfirmationEmail(new Email('jurica.separovic@gmail.com')); // Skips sending
```

## Define abstractions for commands that cross system boundaries

```php
// No abstraction, harder to test and change
class SendMessageToKafka
{
    public function __invoke(Message $message)
    {
        $this->producer->produce($message);
    }
}

interface QueueMessageSender
{
    public function __invoke(Message $message): void
}

class KafkaQueueSender implements QueueMessageSender
{} 
```

