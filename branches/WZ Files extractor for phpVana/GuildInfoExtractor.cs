using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

using System.Windows.Forms;

namespace WZ_Files_extractor_for_phpVana
{
    class GuildInfoExtractor : BaseExtractor
    {
        public GuildInfoExtractor(string extractionDir, string wzDir, WzMapleVersion version) : base(extractionDir, wzDir, version, "GuildEmblem") { }


        public override void Start()
        {
            WzFile file = MapleFileCache.Instance["UI"];

            {
                WzImage guildmark = file.WzDirectory["GuildMark.img"] as WzImage;
                {
                    foreach (var type in guildmark["BackGround"].WzProperties.Where(a => { return a is WzSubProperty; }))
                    {
                        currentID = 100000 + int.Parse(type.Name);
                        foreach (var color in type.WzProperties.Where(a => { return a is WzCanvasProperty; }))
                        {
                            ExportIfExists(exDir + type.Name + Path.DirectorySeparatorChar, color);
                        }
                    }
                }
                {
                    foreach (var subtype in guildmark["Mark"].WzProperties.Where(a => { return a is WzSubProperty; }))
                    {
                        Console.WriteLine("sub type: {0}", subtype.Name);
                        foreach (var type in subtype.WzProperties.Where(a => { return a is WzSubProperty; }))
                        {
                            currentID = 100000 + int.Parse(type.Name);
                            foreach (var color in type.WzProperties.Where(a => { return a is WzCanvasProperty; }))
                            {
                                ExportIfExists(exDir + type.Name + Path.DirectorySeparatorChar, color);
                            }
                        }
                    }
                }
            }
        }
    }
}
