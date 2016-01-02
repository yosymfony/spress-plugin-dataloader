## Data loader plugin for Spress

![Spress 2 ready](https://img.shields.io/badge/Spress%202-ready-brightgreen.svg)

Loads data located at `./src/data/` folder of your site.

**This plugin requires Spress >= 2.0**. If you are using Spress 1.x, go to [1.0.0](https://github.com/yosymfony/spress-plugin-dataloader/tree/v1.0.0) version of the plugin.

### How to install?

Go to your Spress site and add the following to your `composer.json` and run 
`composer update`:

```json
"require": {
    "yosymfony/spress-plugin-dataloader": "2.0.*"
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

```twig
{% for theme in site.data.blogUsers %}
...
{% endfor %}
```
