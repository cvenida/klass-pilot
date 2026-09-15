<script setup>
import VueLottie from 'vue3-lottie'
import welcomeAnimation from '@/assets/welcome-animation.json'
</script>

<template>
  <VueLottie :animation-data="welcomeAnimation" :height="200" :width="200" />
</template>