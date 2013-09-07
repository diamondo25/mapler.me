using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.IO;
using MapleLib.WzLib;
using MapleLib.WzLib.Util;
using MapleLib.WzLib.WzProperties;

using System.Drawing;

namespace WZ_Files_extractor_for_phpVana
{
    public class BaseExtractor
    {
        public string exDir { get; set; }
        public string wzDir { get; set; }
        public WzMapleVersion Version { get; set; }
        protected int currentID = 0;

        const int IMG_W = 36, IMG_H = 36;

        public BaseExtractor(string extractionDir, string wzDir, WzMapleVersion version, string pExtractDir = "")
        {
            Version = version;
            exDir = extractionDir;
            exDir += Path.DirectorySeparatorChar + pExtractDir + Path.DirectorySeparatorChar;
            this.wzDir = wzDir;
        }

        public virtual void Start()
        {

        }

        public void ExportProps(IWzImageProperty pProp, string pName)
        {
            if (pProp.WzProperties == null) return;
            ExportObject(pProp, pName);
            foreach (IWzImageProperty prop in pProp.WzProperties)
            {
                ExportProps(prop, pName + "." + prop.Name);
            }
        }

        protected void ExportObject(IWzImageProperty pProp, string pPrepend)
        {
            foreach (var prop in pProp.WzProperties.Where(
                ip => { return ip is WzCompressedIntProperty || ip is WzByteFloatProperty || ip is WzDoubleProperty || ip is WzStringProperty; }
                ))
            {
                SaveInfo(prop, pPrepend);
            }
        }

        protected void ExportAnimatedObject(string pDir, IWzImageProperty pProp, string pPrepend, bool pOnlyImages = false)
        {
            if (pProp is WzUOLProperty)
            {
                if (((WzUOLProperty)pProp).LinkValue == null) return; // nexan
                SaveInfo(pProp, pPrepend);
                return;
            }

            ExportObject(pProp, pPrepend);

            if (pProp["0"] == null)
            {
                // Try to export non-animated object
                foreach (var prop in pProp.WzProperties)
                {
                    ExportIfExists(pDir, prop, pPrepend); // Will export [pPrepend].[prop.Name].png, if needed. alert.0.arm.png
                }

                return;
            }

            for (int i = 0; ; i++)
            {
                string frame = i.ToString();
                if (pProp[frame] == null) break;

                var frameNode = pProp[frame]; // .../N/

                ExportIfExists(pDir, frameNode, pPrepend); // Will export [pPrepend].[frameNode.Name].png, if needed. alert.0.png
                if (pProp[frame].WzProperties == null) continue;
                foreach (var prop in frameNode.WzProperties)
                {
                    ExportIfExists(pDir, prop, pPrepend + "." + frame); // Will export [pPrepend].[frame].[prop.Name].png, if needed. alert.0.arm.png
                }

                if (!(frameNode is WzUOLProperty))
                {
                    foreach (var prop in frameNode.WzProperties.Where(
                        ip => { return ip is WzCompressedIntProperty || ip is WzByteFloatProperty || ip is WzDoubleProperty || ip is WzStringProperty; }
                        ))
                    {
                        SaveInfo(prop, pPrepend + "." + frame);
                    }

                }
            }
        }

        protected void ExportIfExists(string pDir, IWzImageProperty pCanvas, string pName = null, string pDataName = null)
        {
            if (pCanvas == null || pCanvas.WzValue == null)
                return;

            if (pName == null)
                pName = pCanvas.Name;
            else
                pName += "." + pCanvas.Name;

            if (pDataName == null)
                pDataName = pName;

            if (pCanvas is WzUOLProperty)
            {
                if (((WzUOLProperty)pCanvas).LinkValue == null) return; // nexan
                SaveInfo(pCanvas, pName.Substring(0, pName.LastIndexOf('.')));
                return;
                pCanvas = (IWzImageProperty)((WzUOLProperty)pCanvas).LinkValue;
            }

            for (int i = 0; i < pName.Length; i++)
                if (pName[i] > sbyte.MaxValue)
                {
                    Console.WriteLine("Found korean text: {0}", pName);
                    return;
                }
            
            if (pName.Count(c => { return c > sbyte.MaxValue; }) > 0)
            {
                Console.WriteLine("Found korean text: {0}", pName);
                return;
            }
            
            string tmp = pDir + pName + ".png";
            string tmptmp = RemoveFromBackDirSlash(tmp);
            if (pCanvas is WzCanvasProperty)
            {
                Directory.CreateDirectory(tmptmp);
                if (!File.Exists(tmp))
                {
                    // Save to temp folder (new things)
                    string newfile = tmp.Replace("P:\\Result\\", "P:\\Result\\extract_" + DateTime.Now.ToShortDateString() + "\\");
                    Directory.CreateDirectory(RemoveFromBackDirSlash(newfile));

                    pCanvas.ToPngProperty().PNG.Save(newfile, System.Drawing.Imaging.ImageFormat.Png);


                    pCanvas.ToPngProperty().PNG.Save(tmp, System.Drawing.Imaging.ImageFormat.Png);
                    //Console.WriteLine("New file: {0}", tmp);
                }
            }

            if (pCanvas["origin"] != null)
            {
                SaveVector(pCanvas["origin"] as WzVectorProperty, pDataName + ".origin");
            }

            if (pCanvas["z"] != null)
            {
                SaveInfo(pCanvas["z"], pDataName);
            }

            if (pCanvas["map"] != null)
            {
                foreach (var prop in pCanvas["map"].WzProperties.Where(p => { return p is WzVectorProperty; }))
                {
                    SaveVector(prop as WzVectorProperty, pDataName + ".map." + prop.Name);
                }
            }
        }

        protected void SaveVector(WzVectorProperty pOrigin, string pName)
        {
            if (pOrigin == null)
            {
                return;
            }
            pName = pName.Replace('.', '_');

            SQLData.Instance.AppendRow(currentID, pName, "{VEC}" + pOrigin.X.ToInt().ToString() + ";" + pOrigin.Y.ToInt().ToString());
        }

        protected void SaveInfo(IWzImageProperty pValue, string pName)
        {
            if (pValue == null) return;
            pName = pName.Trim('.').Replace('.', '_');
            string value = pValue.WzValue.ToString();
            if (pValue is WzUOLProperty)
            {
                value = "{UOL}" + ((WzUOLProperty)pValue).Value;
            }
            SQLData.Instance.AppendRow(currentID, pName + "_" + pValue.Name, value);
        }


        protected string RemoveFromBackDirSlash(string pVal)
        {
            return pVal.Remove(pVal.LastIndexOf(Path.DirectorySeparatorChar));
        }

        protected void Down(string pVal)
        {
            exDir += pVal + Path.DirectorySeparatorChar;
        }

        protected void Up()
        {
            if (exDir.EndsWith("" + Path.DirectorySeparatorChar))
                exDir = exDir.Remove(exDir.LastIndexOf(Path.DirectorySeparatorChar));

            exDir = exDir.Remove(exDir.LastIndexOf(Path.DirectorySeparatorChar) + 1);
        }
    }
}
