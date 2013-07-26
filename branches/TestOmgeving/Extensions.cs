using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Runtime.InteropServices;

namespace Mapler_Client
{
    public static class Extensions
    {
        public static byte RollLeft(this byte pThis, int pCount)
        {
            uint overflow = ((uint)pThis) << (pCount % 8);
            return (byte)((overflow & 0xFF) | (overflow >> 8));
        }

        public static byte RollRight(this byte pThis, int pCount)
        {
            uint overflow = (((uint)pThis) << 8) >> (pCount % 8);
            return (byte)((overflow & 0xFF) | (overflow >> 8));
        }

        public static TValue GetOrDefault<TKey, TValue>(this IDictionary<TKey, TValue> pThis, TKey pKey, TValue pDefault)
        {
            TValue result;
            return pThis.TryGetValue(pKey, out result) ? result : pDefault;
        }

        public static string ToByteString(this byte[] pThis, string pSeperator = " ")
        {
            if (pThis.Length == 0) return "";
            string result = "";
            foreach (byte val in pThis)
            {
                result += string.Format("{0:X2}{1}", val, pSeperator);
            }
            if (result.Length > pSeperator.Length)
            {
                result = result.Remove(result.Length - pSeperator.Length);
            }
            return result;
        }

        [DllImport("kernel32.dll", SetLastError = true)]
        [PreserveSig]
        public static extern uint GetModuleFileName
        (
                    [In]
            IntPtr hModule,

                    [Out]
            StringBuilder lpFilename,

                    [In]
            [MarshalAs(UnmanagedType.U4)]
            int nSize
        );
    }
}
