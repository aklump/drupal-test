# Injecting CSS During Testing

In some cases there may be an HTML element that should not be present during testing.  In such cases you can inject CSS during testing that will hide the element.  Beyond that example, you are free to inject any CSS and as long as you use `loadPageByUrl`, then your CSS will be added to each page as soon as it's loaded.

Here's how to inject CSS to every test page of a an End to End test.

        <?php
        public static function setUpBeforeClass() {
            static::injectCSS(".taskcamp-reporter__trigger{display:none !important;}");
        }        
