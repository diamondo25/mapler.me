using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

namespace WZ_Files_extractor_for_phpVana
{
    class MapleFileCache : Dictionary<string, WzFile>
    {
        public static MapleFileCache Instance { get; private set; }

        public string Location { get; private set; }
        public WzMapleVersion Version { get; private set; }

        public static void Init(string pDirectory, WzMapleVersion pVersion)
        {
            if (Instance != null) return;

            Instance = new MapleFileCache()
            {
                Location = pDirectory,
                Version = pVersion
            };

            //foreach (var file in pWZFiles)
            //    Instance.LoadFile(file);
        }

        public void LoadFile(string pName)
        {
            Console.WriteLine("Loading {0}", pName);
            var wz = new WzFile(string.Format("{0}\\{1}.wz", this.Location, pName), Version);
            Console.Write("Parsing... ");
            wz.ParseWzFile();
            Console.WriteLine("DONE. Main nodes: {0}", wz.WzDirectory.GetChildImages().Length);
            this[pName] = wz;
        }

        public WzFile this[string name]
        {
            get
            {
                if (this.ContainsKey(name) && base[name] != null)
                {
                    //Console.WriteLine("Gave my bitch a cookie: {0}", name);
                    return base[name];
                }
                else
                {
                    Console.WriteLine("File not loaded: {0}", name);
                    LoadFile(name);
                    return base[name];
                }
            }
            set
            {
                if (this.ContainsKey(name))
                    base[name] = value;
                else
                    base.Add(name, value);
            }
        }
    }
}
