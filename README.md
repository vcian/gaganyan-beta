## 🚀 Laravel AI Hackathon 🚀
This application for the laravel with AI hackathon project.

### Pre Requisite
<ul>
	<li><code>PHP 8.0 or higher</code></li>
    <li><code>Laravel 10.0 or higher</code></li>
	<li><code>Node 18 or higher</code></li>
	<li><code>Composer v2</code></li>
</ul>

### Installation
<ul>
	<li><code>composer install</code></li>
    <li><code>npm install</code></li>
	<li>Doesn't .env file into root folder then please copy from .env.example to .env <code>cp .env.example .env</code></li>
	<li>Change session driver as database into .env file<code>SESSION_DRIVER=database</code></li>
	<li>Generate a new application key<code>php artisan key:generate</code></li>
	<li>Active the storage link using this command: <code>php artisan storage:link</code></li>
</ul>

### Configuration
- Configure database into <code>.env</code> file.

## TL;DR Command Lists
<code> 
	- git clone https://github.com/vcian/gaganyan-beta.git
	- cd gaganyan-beta
	- git fetch origin main
	- git checkout main
	- composer install
	- cp .env.example .env
	- php artisan key:generate 
	- php artisan migrate --seed
	- php artisan serve
</code>

## CODING STANDARD
Please see [CODINGSTANDARD](CODINGSTANDARD.md) for details.

## Licence
This software is licensed under the Apache 2 license, quoted below.
