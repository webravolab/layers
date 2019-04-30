# WebravoLab Layers

## Changes Log

### Ver. 1.0.42

* Added new functions to CdnService to interact with Google Storage Buckets
    * downloadImageFromCdn
    * createBucket
    * deleteBucket
    * checkBucketExists
* Added HISTORY.md with changes log
 
### Ver. 1.0.41

* ConfigurationService: override settings through DB table could override also a class, using sintax: class.variable

### Ver. 1.0.40

* Configuration: added method:
    * set - to set/overwrite a value in settings table
    * delete - to delete a value from settings table
   
### Ver. 1.0.39

* ConfigurationService: added method:
    * setKey - to set/overwrite a value in settings table
    * deleteKey - - to delete a value from settings table

### Ver. 1.0.38

* AbstractValueObject: removed setValue() method to make value objects immutable.

### Ver. 1.0.37

* StackDriverLogger: use RFC3339_EXTENDED format for timestamps, to include milliseconds.

### Ver. 1.0.36

* ConfigurationService: added try/catch to avoid any error in override

### Ver. 1.0.35

* StackDriverLogger: improved parsing more date formats in message

### Ver. 1.0.34

* StackDriverLogger: parse RFC3339 and ISO date formats included in message and use it as timestamp (to ingest external loggers)

### Ver. 1.0.33

* CdnService: handle gzip compression in upload
* CdnService: handle image deleting either with a normal path or google storage standard format /b/.../o/...
* CdnService: handle errors 204/404

### Ver. 1.0.32

* Fixed README.md

### Ver. 1.0.31

* Added config/rabbitmq.php
* Updated README.md

 

 

 



    
   
    

