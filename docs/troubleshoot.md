Error:

```bash
Warning: require(/usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/vendor/autoload.php): Failed to open stream: No such file or directory in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php on line 16 Warning: require(/usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/vendor/autoload.php): Failed to open stream: No such file or directory in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php on line 16 Fatal error: Uncaught Error: Failed opening required '/usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/vendor/autoload.php' (include_path='.:/usr/local/share/pear') in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php:16 Stack trace: #0 {main} thrown in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php on line 16
```

Fix: 

```bash
composer install
```

---

Error:

```bash
Fatal error: Uncaught Error: Class "Core\Router" not found in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php:31 Stack trace: #0 {main} thrown in /usr/home/jayarathinavel/domains/api.jayarathinavel.serv00.net/public_html/index.php on line 31
```

Fix: Rename the folders `src/core` and `src/features` to `src/Core` and `src/Features`. Also check if the subfolders are starting with a capital letter

---