# How to Enable and Use XHProf in Lychee

XHProf is a profiling tool for PHP applications. This guide explains how to install, configure, and use XHProf in the Lychee project.

---

## Installation

1. **Install XHProf**

Run the following commands to install XHProf and its dependencies:

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php php-xhprof graphviz
```

Verify the installation:

```bash
php -i | grep xhprof
```

If necessary, restart your web server or PHP-FPM service.

2. **Download the Visualization Tool**

Clone the XHProf visualization tool into the `public/vendor` directory:

```bash
git clone git@github.com:preinheimer/xhprof.git ./public/vendor/xhprof
```

3. **Set Up the Configuration**

Copy the sample configuration file and update it:

```bash
cp public/vendor/xhprof/xhprof_lib/config.sample.php public/vendor/xhprof/xhprof_lib/config.php
nano public/vendor/xhprof/xhprof_lib/config.php
```

Update the following settings in the configuration file:

- **Database Credentials:** Set the database credentials to match your environment.
- **Enable `dot_binary`:** Uncomment and configure the `dot_binary` section.
- **Control IPs:** If running locally, set `$controlIPs` to `false`.

---

## Usage

1. **Enable XHProf**

Set the following environment variable in your `.env` file:

```env
XHPROF_ENABLED=true
```

2. **Profile Requests**

Once enabled, every request to your application will be profiled automatically.

3. **View Profiling Results**

Open the following URL in your browser to view the profiling results:

```
<your-host>/vendor/xhprof/xhprof_html/
```

---

*Last updated: December 22, 2025*