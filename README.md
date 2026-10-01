# WebDeploy
 A GitHub hook for automatic deploy to the remote server.

## Usage:

1. Create file e.g. `deploy.php`:

```
<?php
	
  $deploy = new \WebDeploy\GitHub ('GitHub Token', [
    
    'MyRepo' => [
      
      'login' => 'mylogin',
      'destination' => '/var/www/user/data/www/MyRepo',
      
    ],
    
  ], new \Storage\File (), new \Logger ('logs/deploy.log'));
  
  $deploy->deploy ();
```

2. Upload it to your domain folder (e.g. /var/www/user/data/www/api.site.com)
3. Open your GitHub repo settings.
4. Go to "Webhooks" section and press "New webhook".
5. Input your payload to the file (e.g. https://api.site.com/deploy.php).
6. Select "application/json" content type.
7. Press "Add webhook".

Now you can send your queries via your GitHub clients (GitHub Desktop, own servers etc.).


