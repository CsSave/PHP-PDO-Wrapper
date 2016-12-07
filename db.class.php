<?php
/**
 * @author José Luis Rojo Sánchez
 * @email jose@artegrafico.net
 * @web http://wwww.artegrafico.net
 * @copyright 07-12-2016
 * @access public
 * 
 * - Creamos la instancia de nuestra conexion y consultas a nuestra base de datos.
 * $db = new db();
 * 
 * - Consultas con select
 * $rows = $db->select('_table');
 * $rows = $db->select('_table', ['id','=',54]);                 // with clausule where
 * $rows = $db->select('_table', ['id','=',54], 'nombre desc', '');      // with where and order
 * $rows = $db->select('_table', '', 'nombre asc', [0,5]); // with no where
 * $rows = $db->select('_table', '', '', '0,6');                         // with limit values
 * 
 * - En SELECT COUNT igual que un select los parametros
 * $rows = $db->selectCount('_table');
 * - Para devolver el numero de filas de una consulta COUNT usaremos fetchColumn
 * $total = $db->_countRows; 
 * 
 * - Recorregido de una consulta con foreach
 * foreach ($rows as $row) { echo '<br />'.$row["id"].' '.$row["nombre"]; }
 * 
 * - Insertando valores a una tabla 
 * $valores = ['nombre' => 'jose',
 *            'descripcion' => 'escritor',
 *            'estado' => 1];
 * $db->insert('_table', $valores);        
 * 
 * - Update de registros en tabla
 * $valores = ['nombre' => 'manolo',
 *             'descripcion' => 'obrero',
 *             'estado' => '1'];
 * $db->update('_table', $valores, ['id', '=', 57]);
 * 
 * - Borrando registros de una tabla
 * $db->delete('_table', ['id','=',51]);
 * 
 * - Para mostrar un breve debug de la consulta SQL
 * $db->sql();
 * 
 * - Para mostrar el numero de filas afectadas de un INSERT, UPDATE o DELETE o incluso un SELECT (en MySQL)
 * $total = $db->_count;
 * 
 */
 
class db { 
    
    /** 
     *  Variables privadas para establecer la conexion.
     * @engine el motor del servidor de base de datos. 
     * @host origen de los datos. servidor.
     * @dbname el nombre de la base de datos.
     * @user el usuario con acceso a los datos.     
     * @pass la clave del usuario.
     * @charset la codificacion de dichos datos.
     */    
    
    private $db;
    private $engine = _DB_ENGINE; 
    private $host = _DB_HOST; 
    private $database = _DB_NAME; 
    private $user = _DB_USER; 
    private $pass = _DB_PASS; 
    private $charset = _DB_CHARSET;
    
    const _WHERE = ' WHERE ';
    const _ORDER = ' ORDER BY '; 
    const _LIMIT = ' LIMIT ';    
    
    public function __construct(){
        
        // El data source name que es una cadena que tiene una estructura de datos usada para conectar a un origen.
        $dns = $this->engine.':dbname='.$this->database.";host=".$this->host.";charset=".$this->charset;
        
        // Opciones que le pasaremos a la conexion PHP Data Objetcs
        $opt = [
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,  // errores log
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,    // por defecto 
            PDO::ATTR_EMULATE_PREPARES      => false, // ¡¡¡ Solo por seguridad !!!
        ];
        
        $this->db = new PDO( $dns, $this->user, $this->pass, $this->$opt ); 
        
    }
    
    public function query($sql, $params = []){        
        $query = $this->db->prepare($sql);        
        if(count($params)) {
            foreach ($params as $f => $v) {
                $query->bindValue(':' . $f, $v); //echo '<br />:' . $f, $v;
            }
        }
        if ($query->execute()){ 
            $this->_sql = $query;
            $this->_count = $query->rowCount(); // registros afectados
            $this->_countRows = $query->fetchColumn(); // numero de registros 
            return $query;    
        }        
    }    
    
    function select($table, $where = [], $order = NULL, $limit = []) {             
        $this->_query = 'select * from '.$this->table($table).' '.$this->where($where).' '.$this->order($order).' '.$this->limit($limit);
        return $this->query($this->_query, $this->_params);                
    }
    
    function selectCount($table, $where = [], $order = NULL, $limit = []) {             
        $this->_query = 'select count(*) from '.$this->table($table).' '.$this->where($where).' '.$this->order($order).' '.$this->limit($limit);
        return $this->query($this->_query, $this->_params);                
    }
        
    function where($where = []) {        
        if($where) { 
            
            $field = $where[0]; $operator = $where[1]; $value = $where[2];
            $values = [$field => $value];
            
            $this->_where = self::_WHERE.' '.$field.' '.$operator.' :'.$field;   
            $this->_params = $values; // parametro del where            
            return $this->_where; 
        }           
    }
    
    function order($order = NULL){        
        if ($order) { 
            $this->_order =  self::_ORDER.' '.$order;
            return $this->_order;
        }        
    }
    
    function limit($limit = []) {
        if ($limit) { 
            $from = $limit[0]; $to = $limit[1];
            $limit = $from.','.$to;            
            $this->_limit = self::_LIMIT.' '.$limit;
            return $this->_limit; 
        }
    }
    
    public function insert($table, $values = []) { 
        if(count($values)) {           
            
            foreach ($values as $field => $v)
                $ins[] = ':' . $field;
    
            $ins = implode(', ', $ins);
            $fields = implode(', ', array_keys($values));
            
            $this->_query = 'INSERT INTO '.$this->table($table).' ('.$fields.') VALUES ('.$ins.')';
            $this->_params = $values; // parametros
            
            if(!$this->query($this->_query, $this->_params)) { return true; }
                        
        }
       return false;
    }
    
    public function update($table, $values = [], $where = []) {
        if(count($values) && $where) {            
            
            foreach ($values as $field => $v)
                $ins[] =  $field. ' = ' . ':'.$field;
            
            $ins = implode(', ', $ins);                     
            $this->_query = 'UPDATE '.$this->table($table) .' SET '.$ins.' '.$this->where($where);
            $this->_params = array_merge($values, $this->_params); // parametros update+where 
            
            if(!$this->query($this->_query, $this->_params)) { return true; }      
       }
    }        
    
    public function delete($table, $where = []) {
        if($where) {            
            $this->_query = ' DELETE FROM '.$this->table($table).' '.$this->where($where);            
            if(!$this->query($this->_query, $this->_params)) { return true; }
        }      
    }     
    
   	public function count()	{
		return $this->_count;
	}        
    
    public function exec($sql) {
         $query = $this->db->prepare($sql);  
         $query->execute();
    }
            
    protected function table($table) {
        $table = '`'.$table.'`' ;
        return $table;
    }
    
    public function sql(){        
        return var_dump($this->_sql);
    }
                
} ?>   
    
