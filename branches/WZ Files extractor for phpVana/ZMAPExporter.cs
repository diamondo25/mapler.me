using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class ZMAPExporter : BaseExtractor
    {
        public ZMAPExporter(string extractionDir, string wzDir, WzMapleVersion version)
        {
            Version = version;
            exDir = extractionDir;
            exDir += Path.DirectorySeparatorChar;
            this.wzDir = wzDir;
        }

        public void Start()
        {
            WzFile file = MapleFileCache.Instance["Base"];

            WzImage zmaps = file.WzDirectory["zmap.img"] as WzImage;
            StringBuilder sb = new StringBuilder();
            sb.AppendLine("<?php");
            sb.AppendLine("// Generated file @ " + DateTime.Now.ToString());
            sb.AppendLine();
            sb.AppendLine("$zmap = array(");
            foreach (WzNullProperty property in zmaps.WzProperties)
            {
                sb.AppendLine("\t'" + property.Name + "' => " + property.ID + ",");
            }
            sb.AppendLine(");");
            sb.AppendLine("?>");
            File.WriteAllText(exDir + "zmap.php", sb.ToString());
        }
    }
}
