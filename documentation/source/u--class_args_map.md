# Class Arguments Map

This is a powerful, time-saver when understood.

The keys of this array correspond to the class properties.
Each value relates to the passed argument to your classe's `_construct` method.

## Argument is: class instance

If an argument is an instance of a class, then do this:

    'classArgumentsMap' => [
      'core' => '\Drupal\gop\Core',
    ],

`$this->obj->core` will become a fully-mocked instance of _\Drupal\gop\Core_.  All methods will require mocking before they may be called.

### Partial mocks.

You can indicate a class instance should be a [partial mock](http://docs.mockery.io/en/latest/reference/partial_mocks.html) by doing like so:

    'classArgumentsMap' => [
      'core' => ['\Drupal\gop\Core', EasyMock::PARTIAL]
    ],

`$this->obj->core` will become a partially-mocked instance of _\Drupal\gop\Core_.  Method calls will return the actual methods unless you override them.

### If `_construct` calls a method on an arg

In order to accommodate for constructors that call a method on thier arguments, you will need to set up `classArgumentsMap` using a callback, wherein you create the mock and set it's expectations.

If this was the constructor of your class being tested...

    public function __construct(CacheBackendInterface $cache_backend) {
      $this->cache = $cache_backend->get('data');
    }

... you would need to set up your test like this:

    'classArgumentsMap' => [
      'cache_backend' => function () {
        $mock = \Mockery::mock('\Drupal\Core\Cache\CacheBackendInterface');
        $mock->shouldReceive('get')->andReturn('15');
        return $mock;
      },
    ],
    
## Argument is: a value

If a passed argument is a value, do like this:

    'classArgumentsMap' => [
      'age' => [64, self::VALUE],
      'name' => ['Aaron', self::VALUE],
    ],

`$this->obj->age` will become an integer value of `64`.
`$this->obj->name` will become a string value of `Aaron`.

## Argument value at runtime


