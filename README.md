# Filoquent

Filoquent is a powerful Laravel package that allows you to filter Eloquent models using HTTP query parameters seamlessly. This package simplifies the process of building dynamic query filters in your Laravel applications, making it easier to handle complex filtering logic directly through query strings.

## Features

- **Dynamic Query Filtering:** Filter Eloquent models dynamically based on HTTP query parameters.
- **Easy Integration:** Quickly integrate with your existing models and controllers.
- **Extensible:** Create custom filters to extend the package functionality.
- **Supports Relationships:** Filter models based on relationships and nested relationships.
- **Flexible and Configurable:** Configure default behaviors and override them as needed.

## Installation

You can install the package via Composer:

```bash
composer require alimousavi/filoquent
```

## Usage

### Use the Trait

To start using the package, simply use the `Filterable` trait in your Eloquent models:

```php
use AliMousavi\Filoquent\Filterable;

class Post extends Model
{
    use Filterable;
}
```


### Create a Filter Class

Filoquent provides an artisan command to generate filter classes easily:

```bash
php artisan make:filter Blog\PostFilter
```

This command creates a new filter class in the `app/Filters` directory.


### Defining Custom Filters

You can define custom filters by creating filter classes. Each filter class should extend the `FilterAbstract` class:

```php
namespace App\Filters\Blog;

use AliMousavi\Filoquent\Filters\FilterAbstract;

class PostFilter extends FilterAbstract
{

    /**
     * @var array
     *
     * An array of fields that can be filtered. 
     * They key of array is the field being filtered and the value is the type of the field.
     */
    protected array $filterables = [
        'title' => self::TYPE_STRING,
        'author' => self::TYPE_STRING,
    ];

    /**
     * @var array
     *
     * An array of fields that can be searched. 
     */
    protected array $searchables = [
        'title',
        'content'
    ];

    /**
     * @var array
     *
     * An array of fields that can be used for ordering.
     */
    protected array $orderBy = [
        'published_at' => 'desc',
        'title'
    ];
    
    public function title(string $title){
        $this->builder->where('title', 'like', "%$title%");
    }


    public function author(string $author){
        $this->builder->whereHas('author', function ($query) use ($author) {
            $query->where('name', 'like', "%$author%");
        });
    }
}
```

### Applying Filters

In your controller, you can now apply filters based on query parameters:

```php
use Illuminate\Http\Request;
use App\Models\Blog\Post;
use Filters\Blog\PostFilter;
use Http\Resources\Blog\PostResource;

class PostController extends Controller
{
    public function index(Request $request, PostFilter $filter)
    {
        $posts = Post::query()->filter($filter)->paginate();
        
        return PostResource::collection($posts);
    }
}
```

### Search Feature

Filoquent supports searching across multiple columns, including relationships using dot notation.

#### Example: Basic Search

To search across specified fields, include the search query parameter in your request:

```http
GET /api/users?search=john
```

In your filter class, define the `searchables` array:

```php
protected array $searchables = ['name', 'email'];
```

#### Example: Nested Search with Dot Notation

To search within relationships, use dot notation:

```http
GET /api/users?search=doe
```

Define nested searchable fields in your filter class:

```php
protected array $searchables = ['name', 'profile.bio', 'profile.location'];
```

This will search the `name` field on the `users` table and the `bio` and `location` fields on the related `profile` model.

---

### Ordering

Filoquent allows ordering results by fields that **you explicitly allow**, and supports both **simple column sorting** and **custom method-based sorting**.

#### Usage (Request Example)

```
GET /users?order_by=name:asc,created_at:desc
```

* You can provide multiple sort instructions separated by commas.
* Each field can be sorted `asc` or `desc` (`asc` is default if not specified).

#### Define Allowed Fields

In your filter class, define:

```php
protected array $orderables = [
    'name',
    'created_at',
    'custom_field' => 'sortByCustomField',
];
```

Optionally define default sort if the user provides no sort params:

```php
protected array $orderBy = [
    'name' => 'asc',
];
```

#### Custom Sorting Methods

If you need advanced logic, define a method and pass its name in `orderables`.

```php
protected array $orderables = [
    'score' => 'sortByScore',
];

public function sortByScore(string $direction): void
{
    $this->builder->orderByRaw("some_custom_expression $direction");
}
```


## Contributing

Contributions are welcome!

## License

This package is open-sourced software licensed under the [MIT](https://choosealicense.com/licenses/mit/).

## Credits

- [Ali Mousavi](https://github.com/alimousavidev)

