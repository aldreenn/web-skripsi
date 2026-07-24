const readingData = [
    // --- KONTEN LAMA (1-3) ---
    {
        id: 1,
        level: "A2",
        cefr_class: "cefr-a2",
        category: "daily",
        topic: "Campus",
        title: "Library Announcement",
        readingTime: "2 Min Read",
        desc: "Pengumuman singkat tentang perubahan jam buka perpustakaan universitas minggu ini.",
        fullContent: "The university library will be closed this weekend for maintenance. It will close on Friday at 8:00 PM and will reopen on Monday morning at 7:00 AM. \n\nStudents who have borrowed books must return them by Thursday afternoon if the due date is this weekend. We apologize for the inconvenience.",
        questions: [
            {
                q: "What is the main information of the text?",
                options: ["The library is buying new books.", "The library will be closed for a short time.", "Students can study all weekend.", "The library is opening a new floor."],
                answer: 1,
                explanation: "Teks ini menginformasikan bahwa perpustakaan akan ditutup sementara pada akhir pekan ini untuk perbaikan ('closed this weekend for maintenance')."
            },
            {
                q: "When will the library open again?",
                options: ["Friday night.", "Saturday morning.", "Monday morning.", "Thursday afternoon."],
                answer: 2,
                explanation: "Disebutkan secara eksplisit di teks: '...and will reopen on Monday morning at 7:00 AM.'"
            },
            {
                q: "What should students do if their borrowed books are due this weekend?",
                options: ["Return them on Monday.", "Keep them until next week.", "Return them by Thursday afternoon.", "Pay a fine."],
                answer: 2,
                explanation: "Teks menyatakan: 'Students who have borrowed books must return them by Thursday afternoon if the due date is this weekend.'"
            }
        ]
    },
    {
        id: 2,
        level: "B1",
        cefr_class: "cefr-b1",
        category: "daily",
        topic: "Travel",
        title: "My Trip to Bali",
        readingTime: "4 Min Read",
        desc: "Sebuah blog singkat menceritakan pengalaman liburan dan mencicipi makanan tradisional.",
        fullContent: "Last month, I visited Bali with my family. We stayed at a beautiful hotel near Kuta beach. Every morning, we walked along the shore and watched the surfers. \n\nOne of the best parts of the trip was the food. We tried 'Babi Guling' and 'Sate Lilit', which were incredibly delicious. I bought some traditional souvenirs before going home. It was a memorable holiday.",
        questions: [
            {
                q: "Where did the writer stay during the holiday?",
                options: ["In a traditional village.", "Near Kuta beach.", "In the mountains.", "At a friend's house."],
                answer: 1,
                explanation: "Teks menyebutkan: 'We stayed at a beautiful hotel near Kuta beach.'"
            },
            {
                q: "What did the writer do every morning?",
                options: ["Ate traditional food.", "Bought souvenirs.", "Walked along the shore.", "Learned how to surf."],
                answer: 2,
                explanation: "Teks menyebutkan: 'Every morning, we walked along the shore and watched the surfers.'"
            }
        ]
    },
    {
        id: 3,
        level: "B2",
        cefr_class: "cefr-b2",
        category: "academic",
        topic: "Science",
        title: "The Greenhouse Effect",
        readingTime: "6 Min Read",
        desc: "Pelajari bagaimana atmosfer bumi memerangkap panas dan dampaknya terhadap perubahan iklim global.",
        fullContent: "The greenhouse effect is a natural process that warms the Earth's surface. When the Sun's energy reaches the Earth's atmosphere, some of it is reflected back to space and the rest is absorbed and re-radiated by greenhouse gases. \n\nGreenhouse gases include water vapor, carbon dioxide, methane, nitrous oxide, and ozone. Without this process, the Earth would be too cold for life to exist. However, human activities, particularly the burning of fossil fuels, have significantly increased the concentration of these gases.",
        questions: [
            {
                q: "What is the primary function of the greenhouse effect?",
                options: ["To reflect all solar energy back to space.", "To cool down the Earth's surface.", "To warm the Earth's surface.", "To destroy ozone layers."],
                answer: 2,
                explanation: "Kalimat pertama jelas menyatakan: 'The greenhouse effect is a natural process that warms the Earth's surface.'"
            },
            {
                q: "Which of the following is NOT mentioned as a greenhouse gas?",
                options: ["Carbon dioxide", "Methane", "Oxygen", "Water vapor"],
                answer: 2,
                explanation: "Gas yang disebutkan adalah water vapor, carbon dioxide, methane, nitrous oxide, dan ozone. Oxygen tidak disebutkan sebagai gas rumah kaca."
            }
        ]
    },
    // --- KONTEN BARU (4-6) ---
    {
        id: 4,
        level: "B1",
        cefr_class: "cefr-b1",
        category: "academic",
        topic: "History",
        title: "The Industrial Revolution",
        readingTime: "5 Min Read",
        desc: "Membahas transisi menuju proses manufaktur baru di Eropa dan Amerika Serikat.",
        fullContent: "The Industrial Revolution was a period of major industrialization and innovation that took place during the late 1700s and early 1800s. It began in Great Britain and quickly spread throughout the world. \n\nThis era saw the mechanization of agriculture and textile manufacturing and a massive shift from rural, agrarian societies to urban, industrialized ones. The invention of the steam engine was a critical factor in this transformation, powering factories and improving transportation.",
        questions: [
            {
                q: "Where did the Industrial Revolution begin?",
                options: ["The United States", "Great Britain", "France", "Germany"],
                answer: 1,
                explanation: "Teks secara spesifik menyatakan: 'It began in Great Britain and quickly spread throughout the world.'"
            },
            {
                q: "What invention played a critical role in the Industrial Revolution?",
                options: ["The telegraph", "The cotton gin", "The steam engine", "The airplane"],
                answer: 2,
                explanation: "Teks menyebutkan: 'The invention of the steam engine was a critical factor in this transformation...'"
            }
        ]
    },
    {
        id: 5,
        level: "A2",
        cefr_class: "cefr-a2",
        category: "daily",
        topic: "Workplace",
        title: "Office Memo: Dress Code",
        readingTime: "2 Min Read",
        desc: "Memo kantor mengenai pembaruan aturan berpakaian karyawan pada hari Jumat.",
        fullContent: "To: All Employees\nFrom: Human Resources\nDate: October 15\n\nStarting next month, the company will introduce 'Casual Friday'. On Fridays, employees are allowed to wear jeans and t-shirts instead of formal business attire. However, clothes must still be clean and appropriate for the office. Sandals and shorts are strictly prohibited.",
        questions: [
            {
                q: "What is the memo about?",
                options: ["A new manager", "A change in working hours", "A new dress code policy", "A company holiday"],
                answer: 2,
                explanation: "Memo tersebut membahas tentang 'Casual Friday' dan aturan pakaian kerja baru pada hari Jumat."
            },
            {
                q: "What is NOT allowed to be worn on 'Casual Friday'?",
                options: ["Jeans", "T-shirts", "Clean clothes", "Shorts"],
                answer: 3,
                explanation: "Kalimat terakhir dengan jelas melarang celana pendek: 'Sandals and shorts are strictly prohibited.'"
            }
        ]
    },
    {
        id: 6,
        level: "B2",
        cefr_class: "cefr-b2",
        category: "academic",
        topic: "Biology",
        title: "The Process of Photosynthesis",
        readingTime: "7 Min Read",
        desc: "Penjelasan ilmiah tentang bagaimana tumbuhan mengubah cahaya menjadi energi kimia.",
        fullContent: "Photosynthesis is the process used by plants, algae, and certain bacteria to harness energy from sunlight and turn it into chemical energy. Here, organisms transform light energy into ATP and NADPH, which are then used to synthesize complex organic molecules like glucose.\n\nThe process fundamentally requires carbon dioxide, water, and sunlight. Oxygen is released as a byproduct, which is essential for the survival of aerobic organisms on Earth. Chlorophyll, the green pigment in plants, plays a vital role by absorbing the light energy required to drive the reactions.",
        questions: [
            {
                q: "What is the primary purpose of photosynthesis?",
                options: ["To produce water.", "To convert light energy into chemical energy.", "To absorb oxygen.", "To create sunlight."],
                answer: 1,
                explanation: "Kalimat pertama menyebutkan: '...to harness energy from sunlight and turn it into chemical energy.'"
            },
            {
                q: "Which of the following is released as a byproduct of photosynthesis?",
                options: ["Carbon dioxide", "Sunlight", "Oxygen", "Chlorophyll"],
                answer: 2,
                explanation: "Teks menyatakan: 'Oxygen is released as a byproduct, which is essential for the survival of aerobic organisms...'"
            },
            {
                q: "What absorbs the light energy in plants?",
                options: ["Water", "Glucose", "Chlorophyll", "Bacteria"],
                answer: 2,
                explanation: "Teks menyebutkan: 'Chlorophyll, the green pigment in plants, plays a vital role by absorbing the light energy...'"
            }
        ]
    }
];