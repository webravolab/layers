# WebravoLab Layers

Laravel hexagonal architecture - Domain Driven Design abstraction layers

Includes:
* Generic queue handler using either RabbitMQ or Database
* Command bus (using generic queue handler)
    * Generic Command to be extended from your commands
    * Command bus dispatcher and remote command dispatcher
* Event sink (using generic queue handler)
    * Generic Event to be extended from your events
    * Event bus dispatcher and remote event dispatcher
* StackDriver logging (extending Monolog)
* Null logger (for testing)
* Eloquent persistence layer
* Generic configuration helper with override through database settings table
* Cdn image service (using webravolab/cdn package)
* File system service
* Abstract entity to be extended  by your entities 
* Abstract Value Objects and some simple implementations (url, guid, filename)  

## Installation

```composer require webravolab/layers --no-dev```

To perform tests omit the ```--no-dev``` to install the development dependencies.

The package is compatible with Laravel >= 5, not using (at the moment) any real version dependency.

## Configuration

### RabbitMQ Queue Handler

To use **RabbitMQService** you must have a running RabbitMQ server and copy the following file:

copy ```config/rabbitmq.php``` to laravel /config directory and customize configuration to access your RabbitMQ instance.


### Database Queue Handler

**DBQueueService** is a DB based 100% replacement of RabbitMQ. It needs 3 tables to work:
```
jobs_queue
jobs
events
```

You can find the Laravel migrations to build these tables under ```tests/database/migrations```.
Just replace the DB connection from *testbench* to whatever is your database name.

## Testing

PhpUnit test suite is available. It creates a memory based SqLite database instance to create the required tables and perform tests.
It uses the package ```orchestra/testbench``` to simulate a full Laravel environment. 

### Usage

To understand the generic Queue Handler behaviour plese refer to the included tests for usage examples.

To understand the queue **strategies** please refer to RabbitMQ documentation.     

### Notes  
 
Stackdriver logging implementations require google/cloud-logging library and its dependencies.

It also needs a service account json key loaded in the project root directory. 
Refer to Google Cloud authentication docs for the details.
  

## Config helper

**Webravo\Infrastructure\Library\Configuration** is a helper to access environment and config variables 
in a standard way, allowing variables override through a database table called *settings*.

You can find the Laravel migration to build the setting table under ```tests/database/migrations```.

To enable settings override you must set the environment variable SETTINGS_DB_CONNECTION with the name of your DB connection where the settings table is located.
