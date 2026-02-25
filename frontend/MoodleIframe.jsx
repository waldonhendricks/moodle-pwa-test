import React, { useState, useEffect } from 'react';

const MoodleIframe = ({ userSAIDNumber }) => {
    const [iframeUrl, setIframeUrl] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // Replace this with the actual URL where your PHP script is hosted
    const BACKEND_BROKER_URL = 'https://api.smartstart.org.za/get-moodle-url.php';

    useEffect(() => {
        const fetchMoodleUrl = async () => {
            if (!userSAIDNumber) {
                setError("No ID number provided.");
                setIsLoading(false);
                return;
            }

            try {
                const response = await fetch(BACKEND_BROKER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ idnumber: userSAIDNumber })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to fetch Moodle URL');
                }

                if (data.loginurl) {
                    setIframeUrl(data.loginurl);
                } else {
                    throw new Error('Invalid response from server');
                }
            } catch (err) {
                console.error("Moodle Integration Error:", err);
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchMoodleUrl();
    }, [userSAIDNumber]);

    if (isLoading) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-50">
                <p className="text-lg text-gray-600">Loading SmartStart Learning Portal...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="p-4 m-4 text-red-700 bg-red-100 rounded-lg shadow">
                <h3 className="font-bold">Error loading learning portal</h3>
                <p>{error}</p>
                <p className="mt-2 text-sm">Please ensure your SA ID number is registered on the system.</p>
            </div>
        );
    }

    return (
        <div className="w-full h-screen overflow-hidden">
            <iframe
                src={iframeUrl}
                title="SmartStart Moodle Learning Portal"
                className="w-full h-full border-none"
                // Security sandboxing: allows scripts, forms, and same-origin requests required by Moodle
                sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-downloads"
                allow="camera; microphone; display-capture" // Optional: If Moodle uses media plugins
            />
        </div>
    );
};

export default MoodleIframe;