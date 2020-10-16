# An easy and simple way to map objects to arrays
## Installation
```
composer require salamander/array-mapper
```
## Examples
### Basic example
Given a simple object with the following values (JSON):
```
{
    "identifier": "1",
    "name": "my basic object"
}
```
When we pass this as an object (e.g. json_decode) to the map function with the following mapping configuration:
```
$mapping = [
    'id' => 'identifier',
    'name' => 'title',
];
```
the function will map the source oject's *identifier* field to the output's *id* field, the same goes for *title* and *name*. resulting in the following array:
```
[
    'id' => '1',
    'name' => 'my basic object'
]
```