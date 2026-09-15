import { defineStore } from 'pinia'
import {
  getLearningStrands,
  getLearningStrand,
  createLearningStrand,
  updateLearningStrand,
  deleteLearningStrand,
} from '@/services/learningStrandService'

export const useLearningStrandStore = defineStore('learningStrand', {
  state: () => ({
    learningStrands: [],
    currentLearningStrand: null,
    loading: false,
    error: null,
  }),

  getters: {
    allLearningStrands: (state) => state.learningStrands,
    selectedLearningStrand: (state) => state.currentLearningStrand,
    isLoading: (state) => state.loading,
    learningStrandError: (state) => state.error,
  },

  actions: {
    async fetchLearningStrands() {
      this.loading = true
      this.error = null

      try {
        const { data } = await getLearningStrands()

        this.learningStrands = data
      } catch (err) {
        this.error = err.response?.data?.message || err.message
      }

      this.loading = false
    },

    async fetchLearningStrandById(id) {
      this.loading = true
      this.error = null

      try {
        const { data } = await getLearningStrand(id)

        this.currentLearningStrand = data
      } catch (err) {
        console.log(err)
        this.error = err.response?.data?.message || err.message
      }

      this.loading = false
    },

    async addLearningStrand(payload) {
      this.error = null

      try {
        const { data } = await createLearningStrand(payload)

        this.learningStrands.push(data)
      } catch (err) {
        console.log(err)
        this.error = err.response?.data?.message || err.message
      }
    },

    async editLearningStrand(id, payload) {
      this.error = null

      try {
        const { data } = await updateLearningStrand(id, payload)

        const index = this.learningStrands.findIndex((ls) => ls.id === id)
        if (index !== -1) {
          this.learningStrands[index] = data
        }

        if (this.currentLearningStrand?.id === id) {
          this.currentLearningStrand = data
        }
      } catch (err) {
        console.log(err)
        this.error = err.response?.data?.message || err.message
      }
    },

    async removeLearningStrand(id) {
      this.loading = true
      this.error = null

      try {
        await deleteLearningStrand(id)

        this.learningStrands = this.learningStrands.filter((ls) => ls.id !== id)
        if (this.currentLearningStrand?.id === id) {
          this.currentLearningStrand = null
        }
      } catch (err) {
        console.log(err)
        this.error = err.response?.data?.message || err.message
      }

      this.loading = false
    },
  },
})