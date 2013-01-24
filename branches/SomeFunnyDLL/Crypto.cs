using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace System
{
    public class Crypto
    {
        const string ThingsHappen = "I know you'll read this shit, so why would I encrypt it?";

        public static byte[] Encrypt(byte[] pData, byte[] pKey)
        {
            int length = pData.Length;
            for (int i = 0; i < length; i += 2)
            {
                byte tmp = pData[length - i - 1];
                pData[length - i - 1] = pData[i];
                pData[i] = tmp;
            }
            for (int i = 0; i < length; i++)
                pData[length - i - 1] ^= pKey[i % pKey.Length];

            return pData;
        }

        public static byte[] Decrypt(byte[] pData, byte[] pKey)
        {
            int length = pData.Length;
            for (int i = 0; i < length; i++)
                pData[length - i - 1] ^= pKey[i % pKey.Length];

            for (int i = 0; i < length; i += 2)
            {
                byte tmp = pData[length - i - 1];
                pData[length - i - 1] = pData[i];
                pData[i] = tmp;
            }
            return pData;
        }
    }
}
