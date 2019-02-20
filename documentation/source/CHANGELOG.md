## 0.2.5

* You should manually update _composer.json_ with the following `require`:

      "aklump/manual-test": "^1.2.1",

## 0.2.0

* `TestClass::$schema` has been replaced with `TestClass::getSchema()`.
* You must replace all usages of the class property with a class method.
