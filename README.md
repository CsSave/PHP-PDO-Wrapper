# PDO CLASS

easy pdo class to make querys

```php
$db = new db();
```

### make a a query 
```php
$rows = $db->select('id','nombre')
              ->from('_test')
              ->where(['id','=',55])
              ->order('nombre desc')
              ->limit([0,5])
              ->result();
```

### count rows              
```php
$rows = $db->select('count(*))
           ->from('_test')
           ->where(['id','=',55])
           ->result();
echo $db->_count;             
```

### insert rows
```php
$valores = ['nombre' => 'jose',
            'descripcion' => 'escritor',
            'estado' => 1];
 
$rows = $db->select('count(*))
           ->from('_test')
           ->where(['id','=',55])
           ->result();
```

### update rows           
```php
$valores = ['nombre' => 'manolo',
            'descripcion' => 'obrero',
            'estado' => '1'];
$db->update('_test')
   ->set($valores)
   ->where(['id','=',54])
   ->result('update')           
```

### delete a row
```php
$db->delete('_test')
   ->where(['id','=',63])
   ->result('delete');
```

### show SQL query
```php
$db->sql();
```   
