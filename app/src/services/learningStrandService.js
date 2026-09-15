import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const { VITE_API_BASE_URL } = import.meta.env

const getAuthHeaders = () => {
  const authStore = useAuthStore()
  return {
    headers: {
      Authorization: `Bearer ${authStore.token}`,
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  }
}

// Learning Strand Endpoints
export const getLearningStrands = async () => {
  return await axios.get(`${VITE_API_BASE_URL}/learning-strands`, getAuthHeaders())
}

export const getLearningStrand = async (id) => {
  return await axios.get(`${VITE_API_BASE_URL}/learning-strands/${id}`, getAuthHeaders())
}

export const createLearningStrand = async (payload) => {
  return await axios.post(`${VITE_API_BASE_URL}/learning-strands`, payload, getAuthHeaders())
}

export const updateLearningStrand = async (id, payload) => {
  return await axios.put(`${VITE_API_BASE_URL}/learning-strands/${id}`, payload, getAuthHeaders())
}

export const deleteLearningStrand = async (id) => {
  return await axios.delete(`${VITE_API_BASE_URL}/learning-strands/${id}`, getAuthHeaders())
}

// Learning Strand Application Endpoints
export const getLearningStrandApplications = async () => {
  return await axios.get(`${VITE_API_BASE_URL}/learning-strand-applications`, getAuthHeaders())
}

export const getLearningStrandApplication = async (id) => {
  return await axios.get(`${VITE_API_BASE_URL}/learning-strand-applications/${id}`, getAuthHeaders())
}

export const applyForLearningStrand = async (payload) => {
  return await axios.post(`${VITE_API_BASE_URL}/learning-strand-applications`, payload, getAuthHeaders())
}

export const updateApplicationStatus = async (id, payload) => {
  return await axios.put(`${VITE_API_BASE_URL}/learning-strand-applications/${id}/status`, payload, getAuthHeaders())
}

export const deleteLearningStrandApplication = async (id) => {
  return await axios.delete(`${VITE_API_BASE_URL}/learning-strand-applications/${id}`, getAuthHeaders())
}