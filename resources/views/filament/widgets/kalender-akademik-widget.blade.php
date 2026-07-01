<x-filament-widgets::widget class="h-full">
    <!-- Widget kalender akademik -->
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 h-full">
            <div class="p-4">
                <h2 class="text-xl font-bold text-center mb-4">Kalender Akademik 2024/2025</h2>

                <!-- Container dengan overflow scroll -->
                <div class="overflow-auto" style="max-height: 500px; position: relative;">
                    <!-- Petunjuk scroll -->
                    <div class="absolute top-2 right-2 bg-primary-600/90 text-white px-2 py-1 rounded-md text-xs z-10 animate-pulse">
                        Scroll untuk melihat lengkap
                    </div>

                    <!-- Gambar Kalender Akademik yang dapat diklik dan di-scroll -->
                    <img
                        src="{{ asset('images/kalenderakademik.jpeg') }}"
                        alt="Kalender Akademik"
                        class="w-full h-auto object-contain cursor-pointer"
                        x-data="{}"
                        x-on:click="$dispatch('open-modal', { id: 'kalender-akademik-modal' })"
                    />
                </div>

                <!-- Tombol untuk memperbesar -->
                <div class="mt-3 flex justify-center">
                    <button
                        x-data="{}"
                        x-on:click="$dispatch('open-modal', { id: 'kalender-akademik-modal' })"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md flex items-center space-x-2 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                        <span>Lihat Ukuran Penuh</span>
                    </button>
                </div>
            </div>
        </div>

    <!-- Modal untuk memperbesar gambar kalender akademik -->
    <div
        x-data="{
            isOpen: false,
            scale: 1,
            panEnabled: false,
            panX: 0,
            panY: 0,
            startX: 0,
            startY: 0,

            toggleModal() {
                this.isOpen = !this.isOpen;

                // Reset zoom dan pan saat modal dibuka
                if (this.isOpen) {
                    this.scale = 1;
                    this.panX = 0;
                    this.panY = 0;
                    document.body.classList.add('overflow-hidden');
                } else {
                    document.body.classList.remove('overflow-hidden');
                }
            },

            zoomIn() {
                this.scale = Math.min(this.scale + 0.25, 3);
            },

            zoomOut() {
                this.scale = Math.max(this.scale - 0.25, 0.5);
            },

            resetZoom() {
                this.scale = 1;
                this.panX = 0;
                this.panY = 0;
            },

            startPan(e) {
                this.panEnabled = true;
                this.startX = e.clientX - this.panX;
                this.startY = e.clientY - this.panY;
            },

            pan(e) {
                if (!this.panEnabled) return;
                this.panX = e.clientX - this.startX;
                this.panY = e.clientY - this.startY;
            },

            endPan() {
                this.panEnabled = false;
            },

            closeOnEscape(e) {
                if (e.key === 'Escape') {
                    this.toggleModal();
                }
            }
        }"
        x-on:open-modal.window="if ($event.detail.id === 'kalender-akademik-modal') toggleModal()"
        x-on:keydown.window="closeOnEscape"
        x-show="isOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
    >
        <div
            @click.outside="toggleModal()"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="transform scale-90 opacity-0"
            x-transition:enter-end="transform scale-100 opacity-100"
            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col"
        >
            <!-- Header dengan tombol close dan kontrol zoom -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    Kalender Akademik 2024/2025
                </h3>

                <div class="flex items-center space-x-2">
                    <!-- Kontrol zoom -->
                    <div class="flex items-center space-x-1 mr-4 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button @click="zoomOut()" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <span class="text-sm font-medium" x-text="`${Math.round(scale * 100)}%`"></span>
                        <button @click="zoomIn()" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                        <button @click="resetZoom()" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded" title="Reset zoom">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tombol tutup -->
                    <button
                        @click="toggleModal()"
                        class="text-gray-500 hover:text-gray-700 focus:outline-none"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Konten modal dengan gambar yang lebih besar dan dapat di-zoom/pan -->
            <div class="flex-1 overflow-auto p-4">
                <div
                    class="relative h-full w-full overflow-auto"
                    @mousedown="startPan"
                    @mousemove="pan"
                    @mouseup="endPan"
                    @mouseleave="endPan"
                >
                    <div
                        class="transform origin-center transition-transform duration-75"
                        :style="`transform: scale(${scale}) translate(${panX}px, ${panY}px);`"
                    >
                        <img
                            src="{{ asset('images/kalenderakademik.jpeg') }}"
                            alt="Kalender Akademik"
                            class="max-w-full h-auto mx-auto"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer dengan petunjuk -->
            <div class="px-4 py-2 border-t dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
                <p class="text-center">Gunakan tombol + dan - untuk memperbesar/perkecil. Drag gambar untuk menggeser.</p>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-filament-widgets::widget>
