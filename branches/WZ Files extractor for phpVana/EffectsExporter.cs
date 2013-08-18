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
    class EffectsExporter : BaseExtractor
    {
        public EffectsExporter(string extractionDir, string wzDir, WzMapleVersion version)
        {
            Version = version;
            exDir = extractionDir;
            exDir += Path.DirectorySeparatorChar + "Effects" + Path.DirectorySeparatorChar;
            this.wzDir = wzDir;
        }

        public void Start()
        {
            WzFile file = MapleFileCache.Instance["Effect"];


            this.currentID = 91000000;
            ExportImage(file.WzDirectory["ItemEff.img"] as WzImage);
            this.currentID = 92000000;
            ExportImage(file.WzDirectory["SetEff.img"] as WzImage);
            this.currentID = 93000000;
            ExportImage(file.WzDirectory["CharacterEff.img"] as WzImage);
            this.currentID = 94000000;
            ExportImage(file.WzDirectory["SetItemInfoEff.img"] as WzImage);
        }

        private void ExportImage(WzImage pImage)
        {
            Action<IWzImageProperty, string, string> RecursiveDown = null;
            RecursiveDown = (prop, name, dataname) =>
             {
                 foreach (IWzImageProperty prop2 in prop.WzProperties)
                 {
                     ExportIfExists(exDir, prop2, name, dataname + "." + prop2.Name);
                     if (prop2.WzProperties == null) continue;
                     ExportObject(prop2, dataname + "." + prop2.Name);
                     RecursiveDown(prop2, name + "." + prop2.Name, dataname + "." + prop2.Name);
                 }
             };

            Down(pImage.Name);
            foreach (IWzImageProperty prop in pImage.WzProperties)
            {
                Down(prop.Name);
                string dataname = prop.Name;
                foreach (IWzImageProperty prop2 in prop.WzProperties)
                {
                    ExportIfExists(exDir, prop2, prop2.Name, dataname + "." + prop2.Name);
                    if (prop2.WzProperties == null) continue;
                    ExportObject(prop2, dataname + "." + prop2.Name);
                    RecursiveDown(prop2, prop2.Name, dataname + "." + prop2.Name);
                }
                Up();
            }
            Up();
        }

        private void ExportProperties(IWzImageProperty pProp, string pName)
        {
            foreach (IWzImageProperty prop in pProp.WzProperties)
            {
                ExportIfExists(exDir, prop, pName + "." + prop.Name);
                if (prop.WzProperties != null)
                {
                    ExportAnimatedObject(exDir, prop, pName);
                    ExportProperties(prop, pName + "." + prop.Name);
                }
            }
        }
    }
}
