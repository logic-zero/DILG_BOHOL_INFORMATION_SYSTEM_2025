<script setup>
import GuestLayout from "../../Layouts/GuestLayout.vue"

defineOptions({ layout: GuestLayout});
</script>

<template>
    <div class="p-2 lg:p-6 w-full">
        <div class="text-center mb-6">
            <img src="/img/Background.png" class="max-w-full h-auto mx-auto" alt="Background Image">
        </div>
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            CONTACT INFORMATION
        </h1>
        <div class="flex flex-col md:flex-row gap-6">
            <div class="w-full md:w-1/2">
                <h1 class="font-bold text-4xl">Get in touch with us!</h1>
                <p class="mt-4 text-lg">If you have any inquiries regarding our programs and services, please do not hesitate to contact us. We make every effort to answer any questions you may have and provide assistance with any of your needs as much as possible. Please contact us if you require any further information or would like to discuss anything related to our office.</p>
                <div class="flex flex-col md:flex-row gap-6 mt-5 justify-center">
                    <div class="text-center w-full md:w-1/2 max-w-md mx-auto">
                        <img src="/img/place.png" alt="location icon" class="h-20 w-16 mx-auto object-contain">
                        <h1 class="text-lg font-bold mt-2">OFFICE ADDRESS</h1>
                        <p class="text-sm px-4">Rajah Sikatuna Avenue, Dampas, Tagbilaran City 6300</p>
                    </div>
                    <div class="text-center w-full md:w-1/2 max-w-md mx-auto">
                        <img src="/img/phone.png" alt="contact icon" class="h-20 w-16 mx-auto object-contain">
                        <h1 class="text-lg font-bold mt-2">CONTACT US</h1>
                        <div class="text-sm px-4">
                            <p>Globe - 09178967572</p>
                            <p>Smart - 09399302989</p>
                            <p>Landline - (038) 422-8038</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 aspect-video">
                <iframe class="w-full h-full border-0 rounded"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.1757461435755!2d123.8735479!3d9.6530238!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa4d0045215a9d%3A0x9061d57f8098ab66!2sDepartment%20of%20the%20Interior%20and%20Local%20Government%20-%20Bohol%20Province%20Region-7%20Office!5e0!3m2!1sen!2sph!4v1709012345678!5m2!1sen!2sph"
                    allowfullscreen loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</template>
