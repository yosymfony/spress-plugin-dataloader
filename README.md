## Data loader plugins for Spress

Loads data located at `./src/data/` folder of your site.
This plugin requires Spress 2.x.

### How to install?

Go to your Spress site and add the following to your `composer.json` and run 
`composer update`:

```
"require": {
    "yosymfony/spress-plugin-dataloader": "~2.0"
}
```

### How to use?

Go to your Spress site an create `./src/data` folder. In this folder you can to create
[JSON](http://en.wikipedia.org/wiki/JSON) that will be available in `site.data.<yourFilename>`.

Example with a users array:

```
./src/data/
|- blogUsers.json
```

In your Twig templates, you can access to this data:

```
{% for theme in site.data.blogUsers %}
...
{% endfor %}
```