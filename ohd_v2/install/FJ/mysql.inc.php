<?
    class db
    {
         var $db_name;
         var $b_newlink;
         var $link;
         var $host;
         var $user;
         var $password;

         var $id_sql;
         var $num_rows;
         var $affected_rows;
         var $num_fields;
         var $result;
         var $id_last;

         var $error;
         var $errorno;

         var $debug;

         var $fetch_mode;

         //--------------------------------------------------------------------
         //  онструктор класса 
         //
         //--------------------------------------------------------------------

         function db($db_name = "dbm_helpdesk", $host = "mysql21.secureserver.net", $user = "dbm_helpdesk", $password = "enableme", $b_newlink = false)
         {
              $this->db_name=$db_name;
              $this->host=$host;
              $this->user=$user;
              $this->password=$password;
              $this->b_newlink=$b_newlink;
              $this->fetch_mode=MYSQL_ASSOC;

              $this->connect();
              return true;
         }

         //------------------          END          --------------------------

         //--------------------------------------------------------------------
         // 
         //
         //--------------------------------------------------------------------

         function connect()
         {
              if (!($this->link=@mysql_connect($this->host, $this->user,
                                               $this->password)))
              {
                   print " Error mysql connect !";
                   exit();
              }

              if (!mysql_select_db($this->db_name, $this->link))
              {
                   die ("Can't select DB ".$this->db_name);
              }
         }

         function close()
         {
              mysql_close ($this->link);
         }
         //------------------          END          --------------------------

         //--------------------------------------------------------------------
         // ‘ункци€ выполн€ет sql запрос к базе данный
         // передаваемый параметр строка запроса
         //--------------------------------------------------------------------

         function exec_query($query)
         {
              $this->id_last=false;

              if ($this->debug)
              {
                   print "Query text : ";
                   print htmlspecialchars($query)."<br>";
              }

              $this->id_sql=@mysql_query($query, $this->link);

              $this->error=@mysql_error($this->link);
              $this->error_no=@mysql_errno($this->link);

              if (!($this->error_no))
              {
                   if (!is_bool($this->id_sql))
                   {
                        $this->num_rows=@mysql_num_rows($this->id_sql);
                        $this->num_fields=@mysql_num_fields($this->id_sql);
                   }

                   $this->affected_rows=@mysql_affected_rows($this->link);
                   $this->id_last=@mysql_insert_id($this->link);
              }

              if ($this->debug)
              {
                   if ($this->error_no)
                   {
                        print "<b>Error in query : ";
                        print $this->error."</b><br>";
                   }
                   else
                   {
                        print "Query : OK<BR>";
                   }
              }

              return !$this->error_no;
         }

         //------------------          END          --------------------------

         //--------------------------------------------------------------------
         // ¬озвращает следующие значение из сделанного запроса
         //
         //--------------------------------------------------------------------

         function get_data()
         {
              $sql=$this->id_sql;

              $result=@mysql_fetch_array($sql, $this->fetch_mode);

              $this->error=@mysql_error($this->link);
              $this->error_no=@mysql_errno($this->link);

              $this->result=$result;

              if ($this->debug)
              {
                   if ($this->error_no)
                   {
                        print "<b>Error in get_data :";
                        print $this->error."</b><br>";
                   }
              }

              return $result;
         }
         //------------------          END          --------------------------
         //--------------------------------------------------------------------
         // 
         //
         //--------------------------------------------------------------------

         function field_name($field_index)
         {
              $result=@mysql_field_name($this->id_sql, $field_index);
              $this->error=@mysql_error();
              $this->error_no=@mysql_errno();

              if ($this->debug)
              {
                   if ($this->error_no)
                   {
                        print "<b>Error in field_name :";
                        print $this->error."</b><br>";
                   }
              }

              return $result;
         }

         function reset_data_pointer()
         {
              @mysql_data_seek($this->id_sql, 0);

              $this->error=@mysql_error();
              $this->error_no=@mysql_errno();

              if ($this->debug)
              {
                   if ($this->error_no)
                   {
                        print "<b>Error in data_seek :";
                        print $this->error."</b><br>";
                   }
              }
         }
         //------------------          END          --------------------------

         //--------------------------------------------------------------------
         // 
         //
         //--------------------------------------------------------------------

         function seek_data_pointer($num)
         {
              $this->reset_data_pointer();

              @mysql_data_seek($this->id_sql, $num);

              $this->error=@mysql_error();
              $this->error_no=@mysql_errno();

              if ($this->debug)
              {
                   if ($this->error_no)
                   {
                        print "<b>Error in data_seek :";
                        print $this->error."</b><br>";
                   }
              }
         }

         //------------------          END          --------------------------
         function str_escaped($str)
         {
              return "'".mysql_escape_string($str)."'";
         }

         function str_binary($str)
         {
              $len=strlen($str);
              $res='';

              for ($i=0; $i < $len; $i++)
              {
                   $res.=str_pad(dechex(ord(substr($str, $i, $i + 1))), 2, 0,
                                 STR_PAD_LEFT);
              }

              return '0x'.$res;
         }
    }
?>