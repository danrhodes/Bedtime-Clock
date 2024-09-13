<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedtime Clock</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.26.0/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
    </style>
</head>
<body>
    <div id="root"></div>

    <script type="text/babel">
        const MoonIcon = ({ className }) => (
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
            </svg>
        );

        const SunIcon = ({ className }) => (
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2"/>
                <path d="M12 20v2"/>
                <path d="m4.93 4.93 1.41 1.41"/>
                <path d="m17.66 17.66 1.41 1.41"/>
                <path d="M2 12h2"/>
                <path d="M20 12h2"/>
                <path d="m6.34 17.66-1.41 1.41"/>
                <path d="m19.07 4.93-1.41 1.41"/>
            </svg>
        );

        const BedtimeClock = () => {
            const [currentTime, setCurrentTime] = React.useState(new Date());
            const [bedtime, setBedtime] = React.useState('19:30');
            const [wakeupTime, setWakeupTime] = React.useState('06:30');
            const [isNightTime, setIsNightTime] = React.useState(false);
            const [isNapTime, setIsNapTime] = React.useState(false);
            const [napDuration, setNapDuration] = React.useState(30);
            const [napEndTime, setNapEndTime] = React.useState(null);
            const [manualOverride, setManualOverride] = React.useState(false);

            React.useEffect(() => {
                const storedBedtime = localStorage.getItem('bedtime');
                const storedWakeupTime = localStorage.getItem('wakeupTime');
                const storedNapDuration = localStorage.getItem('napDuration');
                if (storedBedtime) setBedtime(storedBedtime);
                if (storedWakeupTime) setWakeupTime(storedWakeupTime);
                if (storedNapDuration) setNapDuration(parseInt(storedNapDuration));

                const timer = setInterval(() => {
                    setCurrentTime(new Date());
                }, 1000);

                return () => clearInterval(timer);
            }, []);

            React.useEffect(() => {
                const checkTime = () => {
                    if (manualOverride) return;

                    const now = currentTime;
                    const [bedHour, bedMinute] = bedtime.split(':').map(Number);
                    const [wakeHour, wakeMinute] = wakeupTime.split(':').map(Number);

                    const bedTimeToday = new Date(now);
                    bedTimeToday.setHours(bedHour, bedMinute, 0, 0);

                    const wakeTimeToday = new Date(now);
                    wakeTimeToday.setHours(wakeHour, wakeMinute, 0, 0);

                    if (wakeTimeToday <= bedTimeToday) {
                        if (now >= bedTimeToday || now < wakeTimeToday) {
                            setIsNightTime(true);
                        } else {
                            setIsNightTime(false);
                        }
                    } else {
                        if (now >= bedTimeToday && now < wakeTimeToday) {
                            setIsNightTime(true);
                        } else {
                            setIsNightTime(false);
                        }
                    }

                    if (napEndTime && now < napEndTime) {
                        setIsNapTime(true);
                    } else if (isNapTime) {
                        setIsNapTime(false);
                        setNapEndTime(null);
                    }
                };

                checkTime();
            }, [currentTime, bedtime, wakeupTime, napEndTime, isNapTime, manualOverride]);

            const handleBedtimeChange = (e) => {
                setBedtime(e.target.value);
                localStorage.setItem('bedtime', e.target.value);
                setManualOverride(false);
            };

            const handleWakeupTimeChange = (e) => {
                setWakeupTime(e.target.value);
                localStorage.setItem('wakeupTime', e.target.value);
                setManualOverride(false);
            };

            const handleNapDurationChange = (e) => {
                const duration = parseInt(e.target.value);
                setNapDuration(duration);
                localStorage.setItem('napDuration', duration);
            };

            const startNap = () => {
                const now = new Date();
                const endTime = new Date(now.getTime() + napDuration * 60000);
                setNapEndTime(endTime);
                setIsNapTime(true);
                setManualOverride(true);
            };

            const endNap = () => {
                setIsNapTime(false);
                setNapEndTime(null);
                setManualOverride(false);
            };

            const exitNightMode = () => {
                setIsNightTime(false);
                setManualOverride(true);
            };

            const isDarkMode = isNightTime || isNapTime;

            return (
                <div className={`min-h-screen flex flex-col items-center justify-center transition-colors duration-1000 ${isDarkMode ? 'bg-black text-white' : 'bg-white text-black'}`}>
                    <div className={`w-64 h-64 md:w-80 md:h-80 lg:w-96 lg:h-96 rounded-full border-8 ${isDarkMode ? 'border-blue-400' : 'border-yellow-400'} relative transition-colors duration-1000`}>
                        {[...Array(12)].map((_, i) => {
                            const angle = i * 30;
                            const radian = angle * (Math.PI / 180);
                            const radius = 46;
                            const x = Math.sin(radian) * radius;
                            const y = -Math.cos(radian) * radius;
                            return (
                                <div
                                    key={i}
                                    className="absolute text-lg font-bold"
                                    style={{
                                        transform: `translate(-50%, -50%)`,
                                        left: `calc(50% + ${x}%)`,
                                        top: `calc(50% + ${y}%)`,
                                    }}
                                >
                                    {i === 0 ? 12 : i}
                                </div>
                            );
                        })}
                        <div
                            className="absolute w-0.5 h-2/5 bg-current origin-bottom"
                            style={{
                                transform: `rotate(${(currentTime.getHours() % 12) * 30 + currentTime.getMinutes() * 0.5}deg)`,
                                left: 'calc(50% - 1px)',
                                bottom: '50%',
                            }}
                        />
                        <div
                            className="absolute w-0.5 h-1/2 bg-current origin-bottom"
                            style={{
                                transform: `rotate(${currentTime.getMinutes() * 6}deg)`,
                                left: 'calc(50% - 0.5px)',
                                bottom: '50%',
                            }}
                        />
                        <div className="absolute w-3 h-3 rounded-full bg-current" style={{ left: 'calc(50% - 6px)', top: 'calc(50% - 6px)' }} />
                    </div>
                    <div className="mt-8 text-2xl font-bold">
                        {currentTime.toLocaleTimeString()}
                    </div>
                    {isNightTime ? (
                        <div className="mt-4 flex flex-col items-center fade-in">
                            <MoonIcon className="w-12 h-12 text-blue-400" />
                            <p className="mt-2 text-xl">Sweet dreams!</p>
                            <button
                                onClick={exitNightMode}
                                className="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                            >
                                Exit Night Mode
                            </button>
                        </div>
                    ) : isNapTime ? (
                        <div className="mt-4 flex flex-col items-center fade-in">
                            <MoonIcon className="w-12 h-12 text-blue-400" />
                            <p className="mt-2 text-xl">Enjoy your nap!</p>
                            <p className="mt-1 text-lg">Nap ends at: {napEndTime.toLocaleTimeString()}</p>
                            <button
                                onClick={endNap}
                                className="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                            >
                                End Nap
                            </button>
                        </div>
                    ) : (
                        <div className="mt-4 flex flex-col items-center fade-in">
                            <SunIcon className="w-12 h-12 text-yellow-400" />
                            <p className="mt-2 text-xl">Rise and shine!</p>
                            <div className="mt-4 grid grid-cols-2 gap-4">
                                <div className="flex flex-col">
                                    <label className="mb-1 text-sm font-medium">Bedtime:</label>
                                    <input
                                        type="time"
                                        value={bedtime}
                                        onChange={handleBedtimeChange}
                                        className="px-2 py-1 border rounded text-black"
                                    />
                                </div>
                                <div className="flex flex-col">
                                    <label className="mb-1 text-sm font-medium">Wake-up time:</label>
                                    <input
                                        type="time"
                                        value={wakeupTime}
                                        onChange={handleWakeupTimeChange}
                                        className="px-2 py-1 border rounded text-black"
                                    />
                                </div>
                                <div className="flex flex-col">
                                    <label className="mb-1 text-sm font-medium">Nap duration:</label>
                                    <select
                                        value={napDuration}
                                        onChange={handleNapDurationChange}
                                        className="px-2 py-1 border rounded text-black"
                                    >
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="90">1.5 hours</option>
                                        <option value="120">2 hours</option>
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <button
                                        onClick={startNap}
                                        className="w-full px-4 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                                    >
                                        Start Nap
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        };

        ReactDOM.render(<BedtimeClock />, document.getElementById('root'));
    </script>
</body>
</html>