<?php

use AKlump\DrupalTest\EndToEndTestCase;

/**
 * These are used to generate the documentation examples.
 */
class InteractiveEndToEndTest extends EndToEndTestCase {

  public function testPhoneReceivedAccessCode() {
    $this->assertManual('Assert the website calls your phone with the access code: 66347.');
  }

  public function testPhoneReceivedAccessCode2() {
    $this->assertManual([
      'Assert the website calls your phone',
      'Assert the phone reads the access code: 66347.',
      'Assert robot repeats the number',
    ]);
  }

  public function testPhoneReceivedAccessCode3() {
    $this->assertManual([
      'Assert the website calls your phone',
      'Assert the phone reads the access code: 66347.',
      'Assert robot repeats the number',
    ], [
      'Turn on your phone.',
      'When it rings, answer it.',
      'Write down the access code you hear.'
    ]);
  }

}

