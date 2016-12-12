# PDO CLASS

# easy pdo class to make querys

No tengo razones para crear esta clase ya que PDO es en si ya un wrapper pero si que intento simplificar un poco mas las consultas, sobre todo a la hora de enviar parametros en una consulta insert o update. Seria más cómodo extender la clase PDO pero nunca se sabe si la voy a ampliar para realizar consultas en otras bases de datos diferentes a MySQL.

No soy un crack programando asi que se aceptan sugerencias.

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
