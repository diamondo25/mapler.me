using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;

namespace WZ_Files_extractor_for_phpVana
{
    class SqlFileItemSocket : BaseSQLFile
    {
        public SqlFileItemSocket()
        {
            this._buffer = new StringBuilder(@"
DROP TABLE IF EXISTS `phpVana_socket_info`;

CREATE TABLE `phpVana_socket_info` ( 
  `itemid` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `options` text NOT NULL,
  UNIQUE KEY `itemid` (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
");
            
            this._insert = "INSERT INTO `phpVana_socket_info` (`itemid`, `description`, `options`) VALUES ";
        }


        public static SqlFileItemSocket Instance { get; private set; }

        public static void Init()
        {
            Instance = new SqlFileItemSocket();
        }

        public void AppendRow(int pNebuliteID, string pDescription, string pOptions)
        {
            base.AppendRowInternal(pNebuliteID, pDescription, pOptions);
        }
    }
}
