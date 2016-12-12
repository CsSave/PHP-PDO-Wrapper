# PDO CLASS

easy pdo class to make querys

$db = new db();

// making a a query 
$rows = $db->select('id','nombre')
              ->from('_test')
              ->where(['id','=',55])
              ->order('nombre desc')
              ->limit([0,5])
              ->result();
              
// count rows              
$rows = $db->select('count(*))
           ->from('_test')
           ->where(['id','=',55])
           ->result();
echo $db->_count;             

// insert rows
$valores = ['nombre' => 'jose',
            'descripcion' => 'escritor',
            'estado' => 1];
 
$rows = $db->select('count(*))
           ->from('_test')
           ->where(['id','=',55])
           ->result();

// update rows           
$valores = ['nombre' => 'manolo',
            'descripcion' => 'obrero',
            'estado' => '1'];
$db->update('_test')
   ->set($valores)
   ->where(['id','=',54])
   ->result('update')           

// delete a row
$db->delete('_test')
   ->where(['id','=',63])
   ->result('delete');

// show SQL query
$db->sql();
   
