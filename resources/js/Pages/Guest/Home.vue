<template>
    <div>
        <div class="container mx-auto px-4 py-6 max-w-[1400px]">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Left Column: Google Map & YouTube (Fills Remaining Space) -->
                <div class="flex-1 flex flex-col gap-4 md:w-auto w-full">
                    <!-- Google Map -->
                    <div class="w-full aspect-video">
                        <iframe
                            class="w-full h-full border-0 rounded"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.1757461435755!2d123.8735479!3d9.6530238!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa4d0045215a9d%3A0x9061d57f8098ab66!2sDepartment%20of%20the%20Interior%20and%20Local%20Government%20-%20Bohol%20Province%20Region-7%20Office!5e0!3m2!1sen!2sph!4v1709012345678!5m2!1sen!2sph"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    </div>

                    <!-- YouTube Video -->
                    <div class="w-full aspect-video">
                        <iframe
                            class="w-full h-full border-0 rounded"
                            src="https://www.youtube.com/embed/cwAGIorJD6o"
                            title="Baya't LGOO"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <!-- Right Column: Facebook & Calendar -->
                <div class="flex flex-col gap-4 md:w-[500px] w-full">
                    <!-- Facebook Page -->
                    <div class="h-[300px] md:h-[70%] min-h-[400px]">
                        <iframe
                            class="w-full h-full border-0 rounded overflow-auto"
                            src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fdilgboholprovince&tabs=timeline&width=500&height=800&small_header=false&adapt_container_width=false&hide_cover=false&show_facepile=true"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    </div>

                    <!-- Google Calendar -->
                    <div class="h-[300px] md:h-[30%] min-h-[300px] flex items-center justify-center bg-gray-200 text-gray-500 text-xl font-semibold rounded overflow-hidden">
                        <iframe
                            class="w-full h-full border-0 rounded"
                            src="https://calendar.google.com/calendar/embed?src=your-calendar-id%40group.calendar.google.com&ctz=Asia/Manila"
                            style="border: 0"
                            frameborder="0"
                            scrolling="no"
                        ></iframe>
                    </div>
                </div>
            </div>

            <!-- Weather Widget -->
            <div class="mt-4">
                <div class="rounded" id="ww_17a0c713a0539" v='1.3' loc='id' a='{"t":"responsive","lang":"en","sl_lpl":1,"ids":["wl5320"],"font":"Arial","sl_ics":"one_a","sl_sot":"celsius","cl_bkg":"#0097A7","cl_font":"#FFFFFF","cl_cloud":"#FFFFFF","cl_persp":"#FFFFFF","cl_sun":"#FFC107","cl_moon":"#FFC107","cl_thund":"#FF5722","cl_odd":"#0000000a"}'>
                    <a href="https://weatherwidget.org/" id="ww_17a0c713a0539_u" target="_blank">Html weather widget</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import GuestLayout from "../../Layouts/GuestLayout.vue"

defineOptions({ layout: GuestLayout});

onMounted(() => {
    const script = document.createElement("script");
    script.src = "https://app3.weatherwidget.org/js/?id=ww_17a0c713a0539";
    script.async = true;
    document.body.appendChild(script);
});

</script>
