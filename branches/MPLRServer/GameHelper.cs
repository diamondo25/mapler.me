using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace MPLRServer
{
    class GameHelper
    {
        public static bool IsBeginnerJob(int pJobID)
        {
            return (pJobID % 1000) == 0
                || pJobID == 2001
                || pJobID == 2002
                || pJobID == 3001
                || pJobID == 2003
                || pJobID == 5000
                || pJobID == 2004
                || pJobID == 6000
                || pJobID == 6001;
        }

        public static bool IsExtendedSPJob(int pJobID)
        {
            return (pJobID / 1000 == 3 ||
                pJobID / 100 == 22 || pJobID == 2001 || // Evan
                pJobID / 100 == 23 || pJobID == 2002 || // Mercedes
                pJobID / 100 == 24 || pJobID == 2003 || // Phantom
                pJobID / 100 == 27 || pJobID == 2004 || // Luminous
                pJobID / 100 == 51 || pJobID == 5000 || // Mihile
                pJobID / 100 == 61 || pJobID == 6000 || // Kaiser
                pJobID / 100 == 65 || pJobID == 6001 || // Angelic Buster
                pJobID / 10  == 57 || pJobID == 508  || // Jett
                pJobID / 1000 == 4 // JMS specials: Kanna and Hayato
                );
        }

        public static bool is_ignore_master_level_for_common(int pSkillID)
        {
            bool v1 = false;
            if (pSkillID > 5220014)
            {
                if (pSkillID > 22181004)
                {
                    if (pSkillID <= 33121005)
                    {
                        if (pSkillID == 33121005 || pSkillID == 23120011 || pSkillID == 23121008)
                            return true;
                        v1 = pSkillID == 33120010;
                        goto LABEL_43;
                    }
                    if (pSkillID != 35120014)
                    {
                        v1 = pSkillID == 51120000;
                        goto LABEL_43;
                    }
                }
                else
                {
                    if (pSkillID != 22181004)
                    {
                        if (pSkillID > 5720005)
                        {
                            if (pSkillID == 5720008 || pSkillID == 5720012)
                                return true;
                            v1 = pSkillID == 21120011;
                            goto LABEL_43;
                        }
                        if (pSkillID != 5720005)
                        {
                            if (pSkillID > 5321004)
                            {
                                v1 = pSkillID == 5321006;
                                goto LABEL_43;
                            }
                            if (pSkillID < 5321003)
                            {
                                v1 = pSkillID == 5320007;
                                goto LABEL_43;
                            }
                        }
                    }
                }
                return true;
            }
            if (pSkillID == 5220014)
                return true;
            if (pSkillID > 3220010)
            {
                if (pSkillID <= 4340010)
                {
                    if (pSkillID == 4340010 || pSkillID == 3220012 || pSkillID == 4110012)
                        return true;
                    v1 = pSkillID == 4210012;
                    goto LABEL_43;
                }
                if (pSkillID > 5120012)
                {
                    v1 = pSkillID == 5220012;
                    goto LABEL_43;
                }
                if (pSkillID < 5120011)
                {
                    v1 = pSkillID == 4340012;
                    goto LABEL_43;
                }
                return true;
            }
            if (pSkillID >= 3220009)
                return true;
            if (pSkillID > 2121009)
            {
                if (pSkillID > 2321010)
                {
                    if (pSkillID < 3120010 || pSkillID > 3120012)
                        return false;
                }
                else
                {
                    if (pSkillID != 2321010)
                    {
                        v1 = pSkillID == 2221009;
                        goto LABEL_43;
                    }
                }
                return true;
            }
            if (pSkillID == 2121009 || pSkillID == 1120012 || pSkillID == 1220013)
                return true;
            v1 = pSkillID == 1320011;
        LABEL_43:
            if (v1)
                return true;
            return false;
        }

        public static bool is_skill_need_master_level(int pSkillID)
        {
            if (is_ignore_master_level_for_common(pSkillID)) return false;
            if (pSkillID / 1000000 == 92) return false;

            int tmp = 10000 * pSkillID / 10000;

            if ((pSkillID / 1000000 != 92 || pSkillID % 10000 >= 1) &&
                (tmp / 1000000 == 92) && tmp % 10000 == 0) return false;

            tmp = pSkillID / 10000;
            if (tmp == 8000
              || (tmp % 1000) == 0
              || tmp == 2001
              || tmp == 2002
              || tmp == 3001
              || tmp == 2003
              || tmp == 5000
              || tmp == 2004
              || tmp == 6000
              || tmp == 6001
              || tmp == 4001 // Added!
              || tmp == 4002 
                ) return false;

            if (IsBeginnerJob(tmp)) return false;
            
            int jobtype = GetJobType(tmp);
            if (IsEvanJob(tmp))
            {
                if (jobtype != 9 && jobtype != 10 && pSkillID != 22111001 && pSkillID != 22141002 && pSkillID != 22140000)
                    return false;
            }
            else
            {
                if (pSkillID != 4311003 && pSkillID != 4331002 && pSkillID != 4321006 && pSkillID != 4330009)
                    return jobtype == 4;
            }

            return true;
        }

        public static bool IsEvanJob(int pJobID)
        {
            return (pJobID / 100 == 22 || pJobID == 2001);
        }

        public static int GetJobType(int pJobID)
        {
            if (IsBeginnerJob(pJobID) || (pJobID % 100) == 0 || pJobID == 501 || pJobID == 508)
            {
                return 1;
            }
            else
            {
                int tmp = 0;
                if (pJobID / 10 == 43)
                    tmp = (pJobID - 430) / 2;
                else
                    tmp = pJobID % 10;
                tmp += 2;
                if (tmp >= 2 && (tmp <= 4 || tmp <= 10 && IsEvanJob(pJobID)))
                    return tmp;
                else
                    return 0;
            }

        }

        public static bool IsWildHunter(int pJobID)
        {
            return pJobID / 100 == 33;
        }

        public static ushort GetBagID(int pID, int pInventory)
        {
            return (ushort)((pInventory * 30) + pID);
        }
    }
}
