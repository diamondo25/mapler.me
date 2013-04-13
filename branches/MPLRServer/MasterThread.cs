using System;
using System.Collections.Generic;
using System.Collections.Concurrent;
using System.Linq;
using System.Text;

using System.Threading;

namespace MPLRServer
{
    public class MasterThread
    {
        public class RepeatingAction
        {
            public string Name { get; private set; }
            public bool RepeatIsTicks { get; private set; }
            public ulong Repeat { get; private set; }
            public ulong TicksNextRun { get; private set; }
            public Action<DateTime> Action { get; private set; }
            public DateTime LastRun { get; private set; }
            public DateTime NextRun { get; private set; }
            private bool isInForRemoval = false;

            public RepeatingAction(string pName, Action<DateTime> pAction, ulong pStart, ulong pRepeat, bool pRepeatIsTicks = false)
            {
                Name = pName;
                RepeatIsTicks = pRepeatIsTicks;
                Repeat = pRepeat;
                Action = pAction;

                if (RepeatIsTicks)
                {
                    TicksNextRun = MasterThread.Instance.CurrentTickCount + Repeat;
                }
                else
                {
                    NextRun = MasterThread.CurrentDate.AddMilliseconds(pStart);
                }
            }

            public void TryRun(DateTime pCurrentDate)
            {
                if (isInForRemoval) return;
                if (RepeatIsTicks)
                {
                    if (MasterThread.Instance.CurrentTickCount < TicksNextRun) return;
                    if (Repeat == 0) isInForRemoval = true;
                    else TicksNextRun = MasterThread.Instance.CurrentTickCount + Repeat;
                }
                else
                {
                    if ((pCurrentDate - NextRun).TotalMilliseconds < 0) return;
                    if (Repeat == 0) isInForRemoval = true;
                    else NextRun = MasterThread.CurrentDate.AddMilliseconds(Repeat);
                }
                Action(pCurrentDate);
                LastRun = pCurrentDate;
                if (isInForRemoval)
                {
                    MasterThread.Instance.RemoveRepeatingAction(Name, null);
                }
            }
        }

        private volatile static MasterThread _instance;
        public static MasterThread Instance { get { return _instance; } private set { _instance = value; } }

        private static long _curdate;
        public static DateTime CurrentDate { get { return new DateTime(_curdate); } }

        public ulong TicksPerSecond { get; private set; }
        public int TicksBeforeSleep { get; private set; }
        public ulong CurrentTickCount { get; private set; }
        private volatile bool _stop;
        public bool Stop { get { return _stop; } set { _stop = value; } }
        public string ServerName { get; private set; }
        public int LastAmountOfCallbacks { get; private set; }

        private ConcurrentQueue<Action<DateTime>> _callbacks = new ConcurrentQueue<Action<DateTime>>();
        private List<RepeatingAction> _repeatingActions = new List<RepeatingAction>();

        private Thread _mainThread;
        private Timer _dateUpdator;

        public MasterThread(string pServerName, int pTicksBeforeSleep)
        {
            ServerName = pServerName;
            TicksBeforeSleep = pTicksBeforeSleep;
            Stop = false;

            _dateUpdator = new Timer((a) =>
            {
                Interlocked.Exchange(ref _curdate, DateTime.Now.Ticks);
            }, null, 0, 1000);
        }

        public static void Load(string pServerName, int pTicksBeforeSleep = -100)
        {
            Instance = new MasterThread(pServerName, pTicksBeforeSleep);
            Instance.Init();
        }

        public void Init()
        {
            _mainThread = new Thread(Run);
            _mainThread.IsBackground = true;
            _mainThread.Name = "MasterThread - thread";
            _mainThread.Priority = ThreadPriority.Normal;
            _mainThread.Start();
        }

        public void AddRepeatingAction(RepeatingAction pAction)
        {
            AddCallback((a) =>
            {
                _repeatingActions.Add(pAction);
            });
        }

        /// <summary>
        /// This function removes an repeating action synchronously from the list.
        /// </summary>
        /// <param name="pName">Name of the Repeating Action</param>
        /// <param name="pOnRemoved">Callback when the removal is performed.</param>
        public void RemoveRepeatingAction(string pName, Action<DateTime, string, bool> pOnRemoved)
        {
            RepeatingAction action = _repeatingActions.First((a) => { return a.Name == pName; });
            if (pOnRemoved == null) return;
            if (action == null)
            {
                pOnRemoved(CurrentDate, pName, false);
            }
            else
            {
                AddCallback((a) =>
                {
                    pOnRemoved(a, pName, _repeatingActions.Remove(action)); // Sync
                });
            }
        }

        public void AddCallback(Action<DateTime> pAction)
        {
            _callbacks.Enqueue(pAction);
        }

        private void Run()
        {
            Action<DateTime> action;
            try
            {
                for (CurrentTickCount = 0; !Stop; CurrentTickCount++)
                {
                    LastAmountOfCallbacks = _callbacks.Count;
                    if (LastAmountOfCallbacks > 0)
                    {
                        while (_callbacks.TryDequeue(out action))
                        {
                            try
                            {
                                action(CurrentDate);
                            }
                            catch (Exception ex)
                            {
                                Logger.WriteLine("Caught an exception inside the MainThread thread while running an action. Please, handle the exceptions yourself!\r\n{0}", ex.ToString());
                            }
                        }
                    }
                    // Unused thing atm
                    /*
                    foreach (var ra in _repeatingActions)
                    {
                        try
                        {
                            ra.TryRun(CurrentDate);
                        }
                        catch (Exception ex)
                        {
                            Logger.WriteLine("Caught an exception inside the MainThread thread while running an action. Please, handle the exceptions yourself!\r\n{0}", ex.ToString());
                        }
                    }
                    */
                    if ((int)(CurrentTickCount % int.MaxValue) % (TicksBeforeSleep <= 0 ? 1 : TicksBeforeSleep) == 0)
                    {
                        // Re-calculate amount of ticks required before a simple sleep, to cool down the CPU or forcing more throughput.
                        int lasttbf = TicksBeforeSleep;
                        int sleeptime = 1;
                        if (LastAmountOfCallbacks == 0) // Server is doing nothing, so make the sleep more ocurring
                        {
                            TicksBeforeSleep -= 10;
                            NormalizeTicksBeforeSleep();
                        }
                        else  // increase if needed
                        {
                            TicksBeforeSleep += 10 + (int)Math.Pow(LastAmountOfCallbacks / 5, 1.5);
                            NormalizeTicksBeforeSleep();
                        }

                        if (TicksBeforeSleep < 0)
                        {
                            sleeptime = Math.Abs(TicksBeforeSleep);
                        }

                        Thread.Sleep(sleeptime);
                    }
                }

            }
            catch { }
            _mainThread = null;
        }

        private void NormalizeTicksBeforeSleep()
        {
            TicksBeforeSleep = Math.Max(-200, Math.Min(10000, TicksBeforeSleep));
        }
    }
}