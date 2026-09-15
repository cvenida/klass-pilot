import { defineStore } from 'pinia'
import {
  getLearningStrandApplications,
  applyForLearningStrand,
  updateApplicationStatus,
  deleteLearningStrandApplication,
} from '@/services/learningStrandService'

export const useApplicationStore = defineStore('application', {
  state: () => ({
    applications: [],
    loading: false,
    error: null,
  }),

  getters: {
    allApplications: (state) => state.applications,
    isLoading: (state) => state.loading,
    applicationError: (state) => state.error,
  },

  actions: {
    async fetchApplications() {
      this.loading = true
      this.error = null

      try {
        const { data } = await getLearningStrandApplications()

        if (!data.status) {
          throw new Error(data.message || 'Failed to fetch applications')
        }

        this.applications = data.data.applications
      } catch (err) {
        console.error(err)
        this.error = err.response?.data?.message || err.message
      } finally {
        this.loading = false
      }
    },

    async submitApplication(payload) {
      this.loading = true
      this.error = null

      try {
        const { data } = await applyForLearningStrand(payload)

        if (!data.status) {
          throw new Error(data.message || 'Failed to submit application')
        }

        this.applications.push(data.data.application)
        return data
      } catch (err) {
        console.error(err)
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    },

    async changeStatus(id, status) {
      this.loading = true
      this.error = null

      try {
        const { data } = await updateApplicationStatus(id, { status })

        if (!data.status) {
          throw new Error(data.message || 'Failed to update application status')
        }

        const index = this.applications.findIndex((a) => a.application_id === id)
        if (index !== -1) {
          this.applications[index] = data.data.application
        }

        return data
      } catch (err) {
        console.error(err)
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    },

    async removeApplication(id) {
      this.loading = true
      this.error = null

      try {
        const { data } = await deleteLearningStrandApplication(id)

        if (!data.status) {
          throw new Error(data.message || 'Failed to delete application')
        }

        this.applications = this.applications.filter((a) => a.application_id !== id)
        return data
      } catch (err) {
        console.error(err)
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    },
  },
})