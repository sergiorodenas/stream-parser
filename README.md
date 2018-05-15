# ⚡ Laravel Multi-format Streaming Parser

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

## Usage examples

### XML
```php
StreamParser::xml("https://example.com/users.xml")->each(function(Collection $user){
    dispatch(new App\Jobs\SendEmail($user));
});
```

### JSON
```php
StreamParser::json("https://example.com/users.json")->each(function(Collection $user){
    dispatch(new App\Jobs\SendEmail($user));
});
```

### CSV
```php
StreamParser::csv("https://example.com/users.csv")->each(function(Collection $user){
    dispatch(new App\Jobs\SendEmail($user));
});
```