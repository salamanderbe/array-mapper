# An easy and simple way to map objects to arrays

## Installation

```
composer require salamanderbe/array-mapper
```

## Examples

### Basic example

Given a simple object with the following values (JSON):

```
{
    "identifier": "1",
    "title": "my basic object"
}
```

When we pass this as an object (e.g. json_decode) to the map function with the following mapping configuration:

```
$mapping = [
    'id' => 'identifier',
    'name' => 'title',
];
```

the function will map the source oject's _identifier_ field to the output's _id_ field, the same goes for _title_ and _name_. resulting in the following array:

```
[
    'id' => '1',
    'name' => 'my basic object'
]
```

### Nesting

using the same object as above:

```
{
    "identifier": "1",
    "title": "my basic object"
}
```

We can easily create nested mappings using the a config like the following:

```
$mapping = [
    'id' => 'identifier',
    'nested' => [
        'name' => 'title',
    ]
];
```

This will result in the following array:

```
[
    'id' => '1',
    'nested' => [
        'name' => 'my basic object'
    ]
]
```

### Nested object fields on the source object

When your source object has a child object:

```
{
    "identifier": "1",
    "title": "my basic object",
    "child": {
        "child_name": "my child"
    }
}
```

You can use the `.` notation to access child fields, like the following example config:

```
$mapping = [
    'id' => 'identifier',
    'name' => 'title',
    'child_name' => 'child.child_title',
];
```

The child's _child_title_ field will now be mapped to output's _child_name_ field resulting in the following output:

```
[
    'id' => '1',
    'name' => 'my basic object',
    'child_name' => 'my child'
]
```

### Arrays on the source object

Given the following object:

```
{
    "identifier": "1",
    "title": "my basic object",
    "children": [
        {
            "child_title": "my first child"
        },
        {
            "child_title": "my second child"
        }
    ]
}
```

When we want to map fields contained in child objects we can use the `*` notation to indicate we want a field from an array of objects, for example:

```
$mapping = [
    'id' => 'identifier',
    'name' => 'title',
    'child_names' => 'children.*.child_title',
];
```

Now the _child_names_ field on the output array will contain all the child's _child_title_ fields:

```
[
    'id' => '1',
    'name' => 'my basic object',
    'child_names' => [
        'my first child',
        'my second child'
    ]
]
```
