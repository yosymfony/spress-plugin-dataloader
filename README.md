## Data loader plugin for Spress

![Spress 2 ready](https://img.shields.io/badge/Spress%202-ready-brightgreen.svg)

Loads data located at `./src/data/` folder of your site.

**This plugin requires Spress >= 2.0**. If you are using Spress 1.x, go to [1.0.0](https://github.com/yosymfony/spress-plugin-dataloader/tree/v1.0.0) version of the plugin.

### How to install?

Go to your site folder and input the following command:

```bash
$ spress add:plugin yosymfony/spress-plugin-dataloader
```

### How to use?

Go to your Spress site an create `./src/data` folder. In this folder you can to create
[JSON](https://en.wikipedia.org/wiki/JSON), [YAML](https://en.wikipedia.org/wiki/YAML), [Markdown](https://en.wikipedia.org/wiki/Markdown) or Textfiles that will be available in `site.data.<yourFilenameWithoutExtension>`.

Example with an users array and a Json file:

```
./src/data/
|- blogUsers.json
```

Example with an users array and a Yaml file (the extension `yaml` is valid too):

```
./src/data/
|- blogUsers.yml
```

For markdown files the extension `md` or `markdown` are valid.
Text file extensions are `txt` or `text`. 
Markdown and text files are available as string. 

Access to this data in Twig templates:

```twig
{% for theme in site.data.blogUsers %}
...
{% endfor %}
```
