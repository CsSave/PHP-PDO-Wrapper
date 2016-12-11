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
 * $rows = $db->select('id','nombre')
 *             ->from('_test')
 *             ->where(['id','=',55])
 *             ->order('nombre desc')
 *             ->limit([0,5])
 *             ->result();
 *  
 * - Recorregido de una consulta con foreach
 * foreach ($rows as $row) { echo '<br />'.$row["id"].' '.$row["nombre"]; }
 * 
 * - Insertando valores a una tabla 
 * $valores = ['nombre' => 'jose',
 *            'descripcion' => 'escritor',
 *            'estado' => 1];
 * $db->into('_test')
 *     ->values($valores)
 *     ->insert()        
 * 
 * - Update de registros en tabla
 * $valores = ['nombre' => 'manolo',
 *             'descripcion' => 'obrero',
 *             'estado' => '1'];
 * $db->table('_test')
 *     ->set($valores)
 *     ->where(['id','=',54])
 *     ->update();
 * 
 * - Borrando registros de una tabla
 * $db->from('_test')
 *     ->where(['id','=',56])
 *     ->delete();
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
    
    private $table;
    
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
            //$this->_count = $query->rowCount(); // registros afectados
            //$this->_countRows = $query->fetchColumn(); // numero de registros 
            return $query;    
        }        
    }    
    
    function select(){    
        $this->_select=func_get_args();        
        return $this;                
    }
    
    function result() {
        $this->_sql = ' SELECT '.join(',',$this->_select).' FROM '.$this->_table;  
        if (!empty($this->_where)) $this->_sql .=  $this->_where;
        if (!empty($this->_order)) $this->_sql .= $this->_order;
        if (!empty($this->_limit)) $this->_sql .=  $this->_limit;        
        return $this->query($this->_sql, $this->_params);
    }    
        
    function where($where = []) {        
        if($where) {             
            $field = $where[0]; $operator = $where[1]; $value = $where[2];
            $values = [$field => $value];            
            $this->_where = self::_WHERE.' '.$field.' '.$operator.' :'.$field;   
            $this->_params = $values; // parametro del where            
            return $this; 
        }           
    }
    
    function order($order = NULL){        
        if ($order) { 
            $this->_order =  self::_ORDER.' '.$order;
            return $this;
        }        
    }
    
    function limit($limit = []) {
        if ($limit) { 
            $from = $limit[0]; $to = $limit[1];
            $limit = $from.','.$to;            
            $this->_limit = self::_LIMIT.' '.$limit;
            return $this; 
        }
    }
    
    public function insert(){
        $this->_sql = 'INSERT INTO '.$this->_table.' ('.$this->fields.') VALUES ('.$this->ins.')';
        $this->_params = $this->values; // parametros
        return $this->query($this->_sql, $this->_params);
    }
    
    public function values($values = []) {        
        if(count($values)) {
            foreach ($values as $field => $v)
                $ins[] = ':' . $field;
    
            $this->ins = implode(', ', $ins);
            $this->fields = implode(', ', array_keys($values));
            $this->values = $values;
            return $this;
        }
    }
    
    public function update() {        
        $this->_sql = 'UPDATE '.$this->_table .' SET '.$this->ins.' '.$this->_where;    
        $this->_params = array_merge($this->values, $this->_params); // parametros update+where
        return $this->query($this->_sql, $this->_params);
    }       
    
    public function set($values = []) {        
        if(count($values)) { 
            foreach ($values as $field => $v)
                $ins[] =  $field. ' = ' . ':'.$field;
            
            $this->ins = implode(', ', $ins);   
            $this->values = $values;            
            return $this;    
       }  
    } 
    
    public function delete() {
        if($this->_where) {            
            $this->_sql = ' DELETE FROM '.$this->_table.' '.$this->_where;            
            return $this->query($this->_sql, $this->_params);
        }      
    }     
    
   	public function count()	{
		return $this->_count;
	}        
    
    public function exec($sql) {
         $query = $this->db->prepare($sql);  
         $query->execute();
    }
         
    public function table($table) {
        $this->_table = '`'.$table.'`' ;
        return $this;
    }  
                
    public function from($table) { 
        $this->table($table);
        return $this;
    }

    public function into($table) {
        $this->table($table);
        return $this;
    }
    
    public function sql(){        
        return var_dump($this->_sql);
    }
                
} ?>   
    
