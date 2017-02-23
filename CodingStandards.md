# Coding Standards
This doc contains coding standards for this repo. Try to adhere to these so everyone is doing stuff the same way.

##File Formatting
* Keep the curly bracket on the same line as the class and method declarations. Like this:
    ```
    public function doSomething {
    
    }
    ```
* One space between each of the sections at the top of a class. And one space between methods. And one space at the end of the class after the last method.
    ```
    <?php
    
    use Petual\SDK;
    
    class ClassName {
    	
        public function method1() {
            doSomething();
        }
        
        public function method2() {
            doSomething();
        }
        
    }
    ```

## Method Naming Connections - Prefixes

* set         = will assign a property from a parameter passed - nothing returned
* mount       = will assign a property - no parameter is passed - logic is hidden - nothing returned
* calculate   = similar to mount but means it will be doing some code to create something - parameter might be passed - nothing returned
* get         = will return a property (either directly from a property value or calculated from a property value)
* build       = will generate some object and return that object
* try         = will attempt one thing and do another if the first is not possible (like tryCallService that will call it once - then refresh the token and try again if the first call fails


## SDK Structure

###Models              
(AUTO GENERATED) single objects linked to the table data - do not edit

###Collections         
(AUTO GENERATED) multiple models - do not edit

###Services            
Holds all the logic about reading and writing data to/from the database - plus manipulating models.
Name the file simply like 'App'.
Place all service classes inside of here.
1. SubClass Model      
And any extra columns you might need for exceptions like when you need to attach other service models as a property.
Try to keep exceptions to a minimum but sometimes you have to in order to make a fast query that needs two objects
2. IServiceOptions     
Class to set options to pass to the service
3. IBuilder            
sub class if you need to make a model from scratch on the fly or from json/stdClasses (not from the database)


##Date Handling

* All dates in the database should be stored in UTC timezone
* All dates in the code logic should remain in UTC timezone until sent back to the user
* Dates should be converted to the user's timezone whenever a response is sent back to the user
* Dates from the user should be sent in their timezone with a timezone property to validate what timezone they sent - convert it back into UTC right away.
* In absence of a timezone for the user (shouldn't happen but just in case) use Pacific time as the default since that is our home base