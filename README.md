# Laravel MJML

Build responsive e-mails easily using MJML and Laravel Mailables.

### MJML

MJML is an awesome tool from MailJet that allows us to create responsive emails very easily. For more information on how to use it, head to their documentation [here](https://mjml.io/documentation/#mjml-guides)

## Installation

To install this package, require this package using composer as follows:

`composer require asahasrabuddhe/laravel-mjml`

After composer installs the packages and all the dependencies, publish the package configuration using artisan command:

`php artisan vendor:publish`

Select the laravel-mjml in the list. You will also need to install the MJML CLI:

`npm install --save mjml`

## Getting Started

1. Create a view containing MJML in your resources/views directory.
2. Create a mailable class using artisan command:  `php artisan make:mail MJMLEmail`
3. In the mailable class, replace

    ``` use Illuminate\Mail\Mailable;```

    with

    ```use Asahasrabuddhe\LaravelMJML\Mail\Mailable;```
4. For Laravel 8 and below, in the `build` method, use:

    ```php
    public function build()
    {
        return $this->mjml('view.name')
    }
    ```

    For Laravel 9, in the `content` method:, use:

    ```php
    public function content()
    {
        return new Content(
            view: $this->mjml('view.name')->buildMjmlView()['html'],
        );
    }
    ```

## Configuration

By default, the package will automatically detect the path of the MJML CLI installed locally in the project. In case this does not happen or the MJML CLI is installed globally, please update the configuration file likewise.

That's it! You have successfully installed and configured the MJML package for use. Just create new views and use them in the mailables class.