# Kernel Tests

* Test classnames should follow: _\*KernelTest_
* Kernel tests have a full Drupal bootstrap and access to the database, global functions and constants.

## Data Providers and Kernel Tests

* Bootstrapped Drupal elements, e.g. constants are not available in the data provider methods of a test class.
* Class constants are available, however.
