# ⚡ PHP7 / Laravel Multi-format Streaming Parser

[![Build Status](https://scrutinizer-ci.com/g/Rodenastyle/stream-parser/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Rodenastyle/stream-parser/build-status/master)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/rodenastyle/stream-parser.svg?style=flat-square)](https://packagist.org/packages/rodenastyle/stream-parser)
[![Quality Score](https://img.shields.io/scrutinizer/g/rodenastyle/stream-parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/Rodenastyle/stream-parser/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Rodenastyle/stream-parser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Rodenastyle/stream-parser/?branch=master)
[![License](https://img.shields.io/packagist/l/Rodenastyle/stream-parser.svg)](https://packagist.org/packages/Rodenastyle/stream-parser)

> When it comes to parsing XML/CSV/JSON/... documents, there are 2 approaches to consider:
>
> **DOM loading**: loads all the document, making it easy to navigate and parse, and as such provides maximum flexibility for developers.
>
> **Streaming**: implies iterating through the document, acts like a cursor and stops at each element in its way, thus avoiding memory overkill.
>
> [https://www.linkedin.com/pulse/processing-xml-documents-dom-vs-streaming-marius-ilina/](https://www.linkedin.com/pulse/processing-xml-documents-dom-vs-streaming-marius-ilina)

Thus, when it comes to big files, callbacks will be executed meanwhile file is downloading and will be much more efficient as far as memory is concerned.

## Installation
```
composer require rodenastyle/stream-parser
```

## Recommended usage
Delegate as possible the callback execution so it doesn't blocks the document reading: 

(Laravel Queue based example)
```php
use Tightenco\Collect\Support\Collection;

StreamParser::xml("https://example.com/users.xml")->each(function(Collection $user){
    dispatch(new App\Jobs\SendEmail($user));
});
```

## Practical Input/Code/Output demos

### XML
```xml
<bookstore>
    <book ISBN="10-000000-001">
        <title>The Iliad and The Odyssey</title>
        <price>12.95</price>
        <comments>
            <userComment rating="4">
                Best translation I've read.
            </userComment>
            <userComment rating="2">
                I like other versions better.
            </userComment>
        </comments>
    </book>
    [...]
</bookstore>
```
```php
use Tightenco\Collect\Support\Collection;

StreamParser::xml("https://example.com/books.xml")->each(function(Collection $book){
    var_dump($book);
    var_dump($book->get('comments')->toArray());
});
```
```
class Tightenco\Collect\Support\Collection#19 (1) {
  protected $items =>
  array(4) {
    'ISBN' =>
    string(13) "10-000000-001"
    'title' =>
    string(25) "The Iliad and The Odyssey"
    'price' =>
    string(5) "12.95"
    'comments' =>
    class Tightenco\Collect\Support\Collection#17 (1) {
      protected $items =>
      array(2) {
        ...
      }
    }
  }
}
array(2) {
  [0] =>
  array(2) {
    'rating' =>
    string(1) "4"
    'userComment' =>
    string(27) "Best translation I've read."
  }
  [1] =>
  array(2) {
    'rating' =>
    string(1) "2"
    'userComment' =>
    string(29) "I like other versions better."
  }
}
```

### JSON
```json
[
  {
    "title": "The Iliad and The Odyssey",
    "price": 12.95,
    "comments": [
      {"comment": "Best translation I've read."},
      {"comment": "I like other versions better."}
    ]
  },
  {
    "title": "Anthology of World Literature",
    "price": 24.95,
    "comments": [
      {"comment": "Needs more modern literature."},
      {"comment": "Excellent overview of world literature."}
    ]
  }
]
```
```php
use Tightenco\Collect\Support\Collection;

StreamParser::json("https://example.com/books.json")->each(function(Collection $book){
    var_dump($book->get('comments')->count());
});
```
```
int(2)
int(2)
```
### CSV
```csv
title,price,comments
The Iliad and The Odyssey,12.95,"Best translation I've read.,I like other versions better."
Anthology of World Literature,24.95,"Needs more modern literature.,Excellent overview of world literature."
```
```php
use Tightenco\Collect\Support\Collection;

StreamParser::csv("https://example.com/books.csv")->each(function(Collection $book){
    var_dump($book->get('comments')->last());
});
```
```
string(29) "I like other versions better."
string(39) "Excellent overview of world literature."
```

## License
This library is released under [MIT](http://www.tldrlegal.com/license/mit-license) license.
