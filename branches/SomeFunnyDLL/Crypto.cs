using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Security.Cryptography;

namespace System
{
    public class Crypto
    {
        const string ThingsHappen = "I know you'll read this shit, so why would I encrypt it?";

        public static byte[] Encrypt(byte[] pData, byte[] pKey)
        {
            InitAES();

            int length = pData.Length;
            for (int i = 0; i < length; i += 2)
            {
                byte tmp = pData[length - i - 1];
                pData[length - i - 1] = pData[i];
                pData[i] = tmp;
            }
            for (int i = 0; i < length; i++)
                pData[length - i - 1] ^= pKey[i % pKey.Length];

            AESTransform(pData, pKey);

            return pData;
        }

        public static byte[] Decrypt(byte[] pData, byte[] pKey)
        {
            InitAES();

            AESTransform(pData, pKey);

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


        #region AES

        /// <summary>
        /// 'Secret' 8 * 4 byte long key used for AES encryption.
        /// </summary>
        private static byte[] sSecretKey = new byte[] {
            0x6D, 0x65, 0x12, 0x20,
            0x23, 0x00, 0x77, 0x00,
            0x13, 0x42, 0x00, 0x87,
            0xE9, 0x00, 0x00, 0x55,
            0x00, 0x64, 0x88, 0x42,
            0x27, 0x00, 0x00, 0x89,
            0xA8, 0x00, 0x34, 0x00,
            0xCF, 0x23, 0x00, 0x00,
        };

        private static RijndaelManaged _rijndaelAES = new RijndaelManaged();
        private static ICryptoTransform _transformer = null;

        private static void InitAES()
        {
            if (_transformer == null)
            {
                RijndaelManaged mAES = new RijndaelManaged();
                mAES.Key = sSecretKey;
                mAES.Mode = CipherMode.ECB;
                mAES.Padding = PaddingMode.PKCS7;
                _transformer = mAES.CreateEncryptor();
            }
        }

        /// <summary>
        /// Transforms given buffer with AES + given IV
        /// </summary>
        /// <param name="pData">Data to be transformed</param>
        /// <param name="pIV">IV used to transform data</param>
        private static void AESTransform(byte[] pData, byte[] pIV)
        {
            int remaining = pData.Length;
            int length = 0x5B0;
            int start = 0;
            byte[] realIV = new byte[pIV.Length * 4];
            while (remaining > 0)
            {
                for (int index = 0; index < realIV.Length; ++index) realIV[index] = pIV[index % 4];

                if (remaining < length) length = remaining;
                for (int index = start; index < (start + length); ++index)
                {
                    if (((index - start) % realIV.Length) == 0)
                    {
                        byte[] tempIV = new byte[realIV.Length];
                        _transformer.TransformBlock(realIV, 0, realIV.Length, tempIV, 0);
                        Buffer.BlockCopy(tempIV, 0, realIV, 0, realIV.Length);
                    }
                    pData[index] ^= realIV[(index - start) % realIV.Length];
                }
                start += length;
                remaining -= length;
                length = 0x5B4;
            }
        }

        #endregion
    }
}
