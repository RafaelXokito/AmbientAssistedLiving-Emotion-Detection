#!/bin/bash

# Start the Sail containers
./vendor/bin/sail up -d

# Run the storage:link command inside the Laravel container
./vendor/bin/sail artisan storage:link