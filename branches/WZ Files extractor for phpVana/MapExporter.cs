using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class MapExporter : BaseExtractor
    {
        public MapExporter(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version, "Map") { }


        public override void Start()
        {

        }
    }
}
