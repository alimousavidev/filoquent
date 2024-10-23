# Filtering Eloquent Models Using Filoquent: A Powerful Laravel Package

Eloquent, Laravel’s ORM, is incredibly versatile for managing database queries. However, when building APIs or complex applications, managing filters based on HTTP query parameters can get cumbersome. That’s where **Filoquent** comes in—a package that simplifies and enhances dynamic query filtering for Eloquent models.

In this tutorial, we'll dive deep into **Filoquent**, demonstrating how to use it for filtering, searching, ordering, and even handling nested relations with dot notation.

## Installation

First, install the Filoquent package via Composer:

```bash
composer require alimousavi/filoquent
```

After installation, apply the `Filterable` trait to any model you wish to filter:

```php
use AliMousavi\Filoquent\Filterable;

class Post extends Model
{
    use Filterable;
}
```

Now, let’s explore Filoquent's core features.

## Defining Custom Filters

To get started, generate a new filter class:

```bash
php artisan make:filter PostFilter
```

This creates a filter class in the `app/Filters` directory. This class extends `FilterAbstract`, which provides essential functionalities for filtering. Inside this class, you'll find arrays like `$filterables` and `$searchables`, where you define the fields that can be filtered and searched. Additionally, you'll implement methods corresponding to each key in `$filterables`, allowing you to define the specific filtering logic for each parameter.

For each key in the `$filterables` array, there must be a corresponding method in the filter class. This method should accept a single parameter, which is typed according to the value defined in `$filterables`. Inside this method, you should implement the filtering logic using `$this->builder` to modify the query according to the specified filter criteria.

Here’s a simple example:

```php
namespace App\Filters;

use AliMousavi\Filoquent\Filters\FilterAbstract;

class PostFilter extends FilterAbstract
{
    protected array $filterables = [
        'authorId' => self::TYPE_INTEGER,
        'status' => self::TYPE_STRING,
    ];

    public function authorId(int $authorId)
    {
        $this->builder->where('author_id', $authorId);
    }

    public function status(string $status)
    {
        $this->builder->where('status', $status);
    }
}
```

In this example, we’re allowing filtering on the `author_id` and `status` fields.

### Applying Filters

Now, in your controller, apply the filters like this:

```php
use App\Models\Post;
use App\Filters\PostFilter;

class PostController extends Controller
{
    public function index(PostFilter $filter)
    {
        $posts = Post::filter($filter)->paginate();

        return response()->json($posts);
    }
}
```

You can now filter posts via query parameters like:

```http
GET /api/posts?authorId=5&status=published
```

## Search Feature

Searching is incredibly useful, especially for applications with large datasets. Filoquent allows you to define searchable fields easily.

### Basic Search Example

To add search functionality, specify searchable fields in the `PostFilter` class:

```php
protected array $searchables = ['title', 'content'];
```

In your controller, the search field is automatically applied if the query parameter `search` is included:

```http
GET /api/posts?search=Laravel
```

This will search across the `title` and `content` columns of the `posts` table.

### Nested Search (Dot Notation)

One of Filoquent’s standout features is the ability to search across related models using dot notation.

```php
protected array $searchables = ['title', 'author.name'];
```

In this case, `author.name` refers to the related `author` model’s `name` field. The query will search for the term in both `posts.title` and `authors.name` fields.

Example query:

```http
GET /api/posts?search=John
```

This will search for posts where either the title contains "John" or the related author’s name contains "John".

## Ordering Results

To handle sorting, Filoquent provides flexible ordering options. Define which fields should be ordered in the filter class:

```php
protected array $orderBy = [
    'published_at' => 'desc',
    'title' => 'asc',
];
```

This will order the posts by the title in ascending order, but you can easily change that to descending by updating the $orderBy property.

## Full Example

Here’s a fully implemented `PostFilter` with filtering, searching, nested search, and ordering:

```php
namespace App\Filters;

use AliMousavi\Filoquent\Filters\FilterAbstract;

class PostFilter extends FilterAbstract
{
    protected array $filterables = [
        'authorId' => self::TYPE_INTEGER,
        'status' => self::TYPE_STRING,
    ];

    protected array $searchables = [
        'title',
        'author.name'
    ];

    protected array $orderBy = [
        'published_at' => 'desc',
        'title',
    ];

    public function authorId(int $authorId)
    {
        $this->builder->where('author_id', $authorId);
    }

    public function status(string $status)
    {
        $this->builder->where('status', $status);
    }

}
```

With this setup, you can:

- Filter posts by `author_id` or `status`.
- Search by `title` and `author.name`.
- Sort posts by `published_at` or `title`.

Example requests:
```http
GET /api/posts?search=John&authorId=5&orderBy=title
```
This request filters posts by `author_id`, searches within the `title` and related `author.name`, and orders the results by title.

## Conclusion

Filoquent offers a powerful and flexible way to filter, search, and order Eloquent models using HTTP query parameters. Whether you need basic filtering or more complex nested searches, Filoquent makes the process clean and maintainable. With just a few lines of code, you can integrate robust query filtering into your Laravel applications.

Install Filoquent today and simplify query management in your next project!
