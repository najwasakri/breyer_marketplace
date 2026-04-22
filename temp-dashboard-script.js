
        document.querySelector('.settings-icon').addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = document.querySelector('.settings-dropdown');
            dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const settingsDropdown = document.querySelector('.settings-dropdown');
            const kategoriDropdown = document.querySelector('.kategori-dropdown');
            
            if (!e.target.closest('.settings-wrapper')) {
                settingsDropdown.classList.remove('show');
            }
            if (!e.target.closest('.kategori-wrapper')) {
                kategoriDropdown.classList.remove('show');
            }
        });

        // Auto Slider
        const slider = document.querySelector('.slider');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;

        function nextSlide() {
            currentSlide = (currentSlide + 1) % 3;
            updateSlider();
        }

        function updateSlider() {
            slider.style.transform = 'translateX(-' + (currentSlide * 33.333) + '%)';
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Manual navigation with dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                updateSlider();
            });
        });

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // Add this before the slider code
        const kategoriBtn = document.querySelector('.kategori-btn');
        const kategoriDropdown = document.querySelector('.kategori-dropdown');

        kategoriBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            kategoriDropdown.classList.toggle('show');
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set KATEGORI button as active when dropdown is opened
            kategoriBtn.classList.add('active-page');
        });

        // Close dropdown when clicking outside and return HOME to active
        document.addEventListener('click', (e) => {
            if (!kategoriBtn.contains(e.target) && !kategoriDropdown.contains(e.target)) {
                kategoriDropdown.classList.remove('show');
                kategoriBtn.classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // HUBUNGI button functionality - same as KATEGORI
        document.querySelector('.hubungi-link').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove active-page class from all navigation items
            document.querySelectorAll('.nav-menu a').forEach(link => {
                link.classList.remove('active-page');
            });
            // Set HUBUNGI button as active when modal is opened
            e.target.classList.add('active-page');
            
            const modal = document.getElementById('hubungiModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        });

        document.getElementById('closeBtn').addEventListener('click', () => {
            const modal = document.getElementById('hubungiModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
            
            // Return HOME button to active state when modal closes
            document.querySelector('.hubungi-link').classList.remove('active-page');
            document.querySelector('a[href="#home"]').classList.add('active-page');
        });

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('hubungiModal');
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = "none", 300);
                
                // Return HOME button to active state when modal closes
                document.querySelector('.hubungi-link').classList.remove('active-page');
                document.querySelector('a[href="#home"]').classList.add('active-page');
            }
        });

        // Complaint Modal Functions
        function openComplaintForm(e) {
            e.preventDefault();
            // Close settings dropdown first
            document.querySelector('.settings-dropdown').classList.remove('show');
            
            const modal = document.getElementById('complaintModal');
            modal.style.display = "block";
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeComplaintModal() {
            const modal = document.getElementById('complaintModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = "none", 300);
        }

        function setComplaintFeedback(type, message) {
            const feedback = document.getElementById('complaintFeedback');
            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.className = 'complaint-feedback show ' + type;
        }

        // Handle complaint form submission
        document.getElementById('complaintForm').addEventListener('submit', function(e) {
            const type = document.getElementById('complaintType').value.trim();
            const title = document.getElementById('complaintTitle').value.trim();
            const details = document.getElementById('complaintDetails').value.trim();
            const submitButton = this.querySelector('button[type="submit"]');

            if (!type || !title || !details) {
                e.preventDefault();
                setComplaintFeedback('error', 'Sila lengkapkan semua maklumat yang diperlukan.');
                return;
            }

            setComplaintFeedback('success', 'Aduan sedang dihantar. Sila tunggu sebentar...');

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Menghantar...';
            }
        });

        window.addEventListener('load', function() {
            const feedback = document.getElementById('complaintFeedback');
            if (!feedback || !feedback.classList.contains('show')) {
                return;
            }

            const modal = document.getElementById('complaintModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);
        });

        // Close complaint modal when clicking outside
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('complaintModal');
            if (e.target === modal) {
                closeComplaintModal();
            }
        });

        // Add this to your existing script section
        function viewProfile(e) {
            e.preventDefault();
            window.location.href = 'student_profile.php';
        }

        function viewPaymentHistory(e) {
            e.preventDefault();
            window.location.href = 'payment_history.php';
        }

        function changePassword(e) {
            e.preventDefault();
            window.location.href = 'change_password.php';
        }

        function logout(e) {
            e.preventDefault();
            if(confirm('Adakah anda pasti untuk log keluar?')) {
                window.location.href = 'logout.php';
            }
        }

        // Cart functionality
        let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
        
        function updateCartCount() {
            const cartCount = document.querySelector('.cart-count');
            const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Cart click handler
        document.querySelector('.cart-icon').addEventListener('click', () => {
            window.location.href = 'cart.php';
        });

        // Initialize cart count on page load
        updateCartCount();

        // Chatbot functionality
        function openChatbot() {
            document.getElementById('chatbotModal').style.display = 'block';
            // Scroll to bottom when chat is opened
            setTimeout(() => {
                const chatBody = document.getElementById('chatBody');
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 100);
        }

        function closeChatbot() {
            document.getElementById('chatbotModal').style.display = 'none';
        }

        function askQuestion(question) {
            const chatBody = document.getElementById('chatBody');
            
            // Add user message directly
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message user-message';
            messageDiv.innerHTML =
                '<div class="message-avatar user-avatar">' +
                    '👤' +
                '</div>' +
                '<div class="message-content">' + question + '</div>';
            chatBody.appendChild(messageDiv);
            
            // Add bot response
            setTimeout(() => {
                const botResponse = getBotResponse(question);
                addMessage(botResponse, false);
                // Scroll to bottom
                setTimeout(() => {
                    chatBody.scrollTop = chatBody.scrollHeight;
                }, 100);
            }, 1000);
            
            // Scroll to bottom after question
            setTimeout(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 100);
        }

        function addMessage(message, isUser) {
            const chatBody = document.getElementById('chatBody');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + (isUser ? 'user-message' : 'bot-message');
            
            if (isUser) {
                messageDiv.innerHTML =
                    '<div class="message-avatar user-avatar">' +
                        '👤' +
                    '</div>' +
                    '<div class="message-content">' + message + '</div>';
            } else {
                messageDiv.innerHTML =
                    '<div class="message-avatar bot-avatar">' +
                        '🤖' +
                    '</div>' +
                    '<div class="message-content">' + message + '</div>';
            }
            
            chatBody.appendChild(messageDiv);
            
            // Smooth scroll to bottom with a small delay to ensure content is rendered
            setTimeout(() => {
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 50);
        }

        function getBotResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('beli') || lowerMessage.includes('produk')) {
                return '🛒 BESTNYA! Nak beli produk ye? <br><br>📍 Caranya MUDAH gila:<br>1️⃣ Klik menu "KATEGORI" kat atas tu<br>2️⃣ Pilih course yang awak minat<br>3️⃣ Klik "ADD TO CART" ⬅️ SIMPLE!<br><br>🎯 Lepas tu checkout je! Senang kan? 💯';
            } else if (lowerMessage.includes('bayar') || lowerMessage.includes('payment')) {
                return '💳 WOW! Payment kat sini SUPER convenient! <br><br>🏦 Boleh bayar guna:<br>✅ Online Banking (Maybank, CIMB, Public Bank)<br>✅ FPX - All Malaysian banks<br>✅ Credit/Debit Card<br><br>🔒 100% SECURE & FAST process! Trust me! 💪';
            } else if (lowerMessage.includes('hubungi') || lowerMessage.includes('contact')) {
                return '📞 NAK contact kiteorang? BOLEH je! <br><br>🔥 Cara PANTAS:<br>📱 WhatsApp: 010-250-9941 (REPLY CEPAT!)<br>📧 Email: Cashier.sg@cqbreyer.edu.my<br><br>⏰ Waktu Operation:<br>🌅 8:30 PAGI - 5:30 PETANG<br><br>💯 Confirm kiteorang reply ASAP!';
            } else if (lowerMessage.includes('kategori') || lowerMessage.includes('jenis') || lowerMessage.includes('course')) {
                return '📚 OMG! Course kiteorang POWER habis! <br><br>🔥 Check out kategori HOT ni:<br>💻 CS (Computer System) - Tech lovers!<br>📋 AM (Admin Management) - Business minded!<br>👨‍🍳 CULINARY - Food passionate!<br>⚡ ELECTRICAL - Future engineers!<br>🎯 LAIN-LAIN - Special courses!<br><br>✨ Semua course ada future bright! 🌟';
            } else if (lowerMessage.includes('selamat') || lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                return '🎉 HOYEAH! Selamat datang ke Breyer family! <br><br>🤗 Saya SUPER happy dapat jumpa awak! Nak tanya apa-apa ke? <br><br>💡 Pro tip: Try klik quick questions kat bawah tu untuk shortcut! 🚀';
            } else if (lowerMessage.includes('terima kasih') || lowerMessage.includes('thanks')) {
                return '🥰 Awww, sama-sama! <br><br>🌟 PLEASURE bantu awak! Kalau ada apa-apa lagi, jangan segan-segan tanya ye! <br><br>💪 Kiteorang always ready to help! 24/7 support! ✨';
            } else {
                return '🤔 Hmm, interesting question! <br><br>💭 Saya try faham maksud awak... Tapi maybe boleh elaborate sikit? <br><br>🎯 ATAU try tanya pasal:<br>🛒 Cara beli produk<br>💳 Payment methods<br>📞 Contact details<br>📚 Course categories<br><br>🚀 Saya ready nak help!';
            }
        }

        function handleChatEnter(event) {
            // Input removed - no longer needed
        }

        // Close chatbot when clicking outside
        window.onclick = function(event) {
            const chatbotModal = document.getElementById('chatbotModal');
            if (event.target === chatbotModal) {
                closeChatbot();
            }
        }

        // Simple notification system
        function showChatNotification() {
            const indicator = document.getElementById('chatIndicator');
            if (indicator) {
                indicator.style.display = 'flex';
                
                // Hide after 10 seconds
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 10000);
            }
        }

        // Show notification after 30 seconds
        setTimeout(showChatNotification, 30000);
    
