#!/bin/bash

docker run -it --rm -v "$PWD"/app:/usr/src/myapp -w /usr/src/myapp hw10-php php test.php
