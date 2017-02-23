# PHP Unit Testing Instructions

## Scripts to Run Tests (The Easy Way)
We have a bash script installed in /usr/bin which makes calling phpunit tests really easily. If you want to do it the old way then see below.

Run the following command to run tests
`> phptest`

###Options
1. Specify `-s` to stop on the first failure
1. Specify `-f` to filter for a specific test then include the test name like `-f test1`
1. Specify `-d` to specify the sub directory below /test to use. Include the leading slash. Example: `-d /Gofer/Util`


## How To Run REST Api Calls
To run tests on the REST calls that set headers and echo the response you'll need to add these two lines to the test class in the test php file:
```
protected $preserveGlobalState = FALSE;
protected $runTestInSeparateProcess = TRUE;
```

- The first line will make sure that constants get defined properly. 
- The 2nd line makes sure it launched each test in a new process so the headers aren't already printed from an earlier process


## How to Setup Test Data

1. Empty data before each test begins... leave data alone after the test so it is available for debugging what went wrong

1. Make a copy of each table
    - prefix it with test_ instead of srvc_ 
    - Table should be identical.
    - Use a query like this if you need to create it:
    - create table test_xyz like srvc_xyz;
    - insert into test_xyz select * from srvc_xyz;

1. Then move the data from that table to :
    - See the methods in the TestingUtil class
    - Example:
    
```
TestingUtil::emptyTable('srvc_gofer_emails');`
TestingUtil::moveTestData('test_gofer_emails', 'srvc_gofer_emails', array('email_id'=>'093c322c-357b-449f-a099-02d5475da1ea'));`
```

## How to Test a Private/Protected Method
PHP Unit by befault only let's you call a public method. To call a private/protected method you use the `TestingUtil::getClassPrivateMethod` method

Example: *Note how we create the private method, still create an instance of the class, then invoke that method using the class instance*

```
$search = TestingUtil::getClassPrivateMethod(SalesforceEntityPredictor::class, 'search');
$salesforceEntityPredictor = new SalesforceEntityPredictor($action, $searchValue, $entityId, $intentId);
$salesforceEntityPredictor->setSoslBuilder($soslBuilder)->setStopWords($stopWords);
$search->invokeArgs($salesforceEntityPredictor, []);
$this->assertEquals($exitEarly, $salesforceEntityPredictor->isExitEarly());
```
    
## Run Scripts Manually
#### To run a single test in vagrant:

```
/home/www-gofer-server/gofer-server/src/vendor/phpunit/phpunit/phpunit --bootstrap /home/www-gofer-server/gofer-server/src/vendor/autoload.php /home/www-gofer-server/gofer-server/tests/Class
```
#### To run all tests in a folder:
```
/home/www-gofer-server/gofer-server/src/vendor/phpunit/phpunit/phpunit --bootstrap /home/www-gofer-server/gofer-server/tests/Bootstrap.php /home/www-gofer-server/gofer-server/tests
```

#### To stop on the first failure or error
```
/home/www-gofer-server/gofer-server/src/vendor/phpunit/phpunit/phpunit --stop-on-error --stop-on-failure --bootstrap /home/www-gofer-server/gofer-server/tests/Bootstrap.php /home/www-gofer-server/gofer-server/tests
```

#### To Filter for a Specific Test:
/home/www-gofer-server/gofer-server/src/vendor/phpunit/phpunit/phpunit --bootstrap /home/www-gofer-server/gofer-server/tests/Bootstrap.php /home/www-gofer-server/gofer-server/tests --filter testName

    

    