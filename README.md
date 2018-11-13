# LOE CORE

Contains the base code for interactions with LOE database and file file system.

## Setup

Assumes the existence of a mysql database named

```
LOE
```

with tables:

```
anime
comics
docs
movies
music
tv
```

conection details can be modified in:

```
libs/record/db_php/credentials.php
```

### structure

libs/ -- required submodules

objects/ domain objects base classes [i.e Movie,Song,Episode]

scanners/ holding_bay scanner classes (used to identify holding_bay files)

processors/ holding_bay processor classes (used to process new entries into the LOE)



## Usage

```
require_once __DIR__ . '/factory.php';

//initialize an empty domain object

$movie = LoeFactory::create('movies');

$movie->title = 'Titanic';

//initialize an existing domain object

$song = LoeFactory::create('music',6661);
print_r($song);

```