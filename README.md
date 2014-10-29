PDFDesignerBundle
====================

PDF Designer Bundle for creating PDF templates for [Oro Platform](https://github.com/orocrm/platform) using [wkhtmltopdf](http://wkhtmltopdf.org/).

## Installation

First make sure wkhtmltopdf is installed properly. Recommended way on Ubuntu is to install it with xvfb using a wrapper script to avoid needing X server to run.

Example:
```
apt-get install wkhtmltopdf
apt-get install xvfb
echo 'xvfb-run --server-args="-screen 0, 1024x768x24" /usr/bin/wkhtmltopdf $*' > ./wkhtmltopdf.sh
chmod a+x wkhtmltopdf.sh
sudo mv wkhtmltopdf.sh /usr/bin/wkhtmltopdf.sh
sudo ln -s /usr/bin/wkhtmltopdf.sh /usr/local/bin/wkhtmltopdf
wkhtmltopdf http://www.google.com output.pdf
```

Then add to composer.json:
```
"require": {
  "shopline/oro-pdfdesigner": "dev-master"
}
```

or run:

```
composer require shopline/oro-pdfdesigner
```

