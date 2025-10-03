Frontend & Testing Guide for Donehub PDF Module
1. Overview

This guide explains how to test the HTML to PDF conversion functionality using the API endpoints provided by the HtmlToPdf module.

The backend uses Laravel + Puppeteer to convert HTML files to PDFs accurately.

2. API Endpoint

POST /api/pdf/convert

Headers:
Accept: application/json

Form Data:

file (required): The HTML file you want to convert.

3. Example Requests
Using curl:
curl -X POST http://127.0.0.1:8000/api/pdf/convert \
  -H "Accept: application/json" \
  -F "file=@/path/to/your/file.html"

Response:
{
    "status": "success",
    "message": "File converted successfully to PDF.",
    "data": {
        "download_url": "http://127.0.0.1:8000/storage/converted_pdfs/file_1234567890.pdf",
        "filename": "file_1234567890.pdf",
        "size": 1507,
        "original_filename": "file.html",
        "original_size": 460
    }
}

4. Frontend Integration

Create an HTML form in your frontend to allow users to upload HTML files.

Use JavaScript or Axios to send the file to the /api/pdf/convert endpoint.

On success, display the download_url to allow users to download the generated PDF.

Example using Axios:

const formData = new FormData();
formData.append("file", fileInput.files[0]);

axios.post("/api/pdf/convert", formData, {
  headers: { "Accept": "application/json" }
})
.then(response => {
  console.log("PDF URL:", response.data.data.download_url);
})
.catch(error => {
  console.error("Error converting file:", error);
});

5. Notes

Filenames are sanitized and stored in public/converted_pdfs.

Ensure the backend server is running and Node.js with Puppeteer is installed.

Remote resources (images/CSS) are handled by Puppeteer, so HTML with external links should work.