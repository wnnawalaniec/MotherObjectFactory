<p align="center">
    <img src="https://i.ibb.co/8rTcch6/mother-object-repo-logo-rf.png" alt="logo"/>
</p>

# Mother object factory

This library I created for myself, as I like creating [Mother Objects](https://martinfowler.com/bliki/ObjectMother.html)
in my test code. I've noticed that most of it is just bunch of boilerplate code that could be easily
generated.

A mother object is a testing class that creates example objects for testing purposes, helping to streamline test setup
and reuse fixtures across multiple tests. More on this can be found in link above.

## Install
`composer require --dev wojciech.nawalaniec/mother-object-factory`

## Example

Assume we have class like this in our code, which we use a lot in our tests:
```php
final class User
{
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    private string $name;
}
```

It is very simple class, and creating new instance of this class is as simple as:
```php
$obj = new User('John Wick');
```

Yet, event though it's simple to create an object it's still not the best idea to have dozens of places responsible for
creating `User` objects, as for when constructor of this class will change, many tests will have too.
But there is much better reason why you would avoid creating new instances of `User` object in every test of an object
where `User` is one of dependencies. Imagine class like:
```php
class Post
{
    public static function create(Title $title, Content $content, User $author): void
    {
        // ...
    }
}
```

For class like this we would probably have many tests, as there are 3 arguments. In not every test value of every argument
is evenly special for us. Some tests will focus more on `$title` argument, some on `$content` and some on `$author`.
**Creating instances of each object in each test, can blur an image of what is the goal of that specific test case.**

So when I Want to test this `create` method in context of `$title` param I want to be able to write a test, where `$title`
parameter is the main star, and it catches 99% of reader's attention. I want to make very clear of what is tested.
```php
public function testCreatingPost_TitleIsTooLong_ThrowsException(): void
{
    $tooLongTitle = TitleMother::newObject()
        ->ofLength(Title::MAX_LENGHT+1)
        ->create();
        
    Post::create($tooLongTitle, ContentMother::any(), UserMother::any());
}
```