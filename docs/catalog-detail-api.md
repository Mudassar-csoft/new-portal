# Program and campus detail APIs

These public, read-only endpoints return all active programs and all active campuses. No ID, login, or API token is required. Send `Accept: application/json` with requests.

| Method | Path | Details |
| --- | --- | --- |
| GET | `/api/programs` | All active programs with description, fees, duration, installments, prerequisites, outline URL, and active discounts |
| GET | `/api/campuses` | All active campuses with name, code, type, location, contact information, and lab count |

Each response contains `status: "success"` and a `data` array. The lists are complete and have no pagination. Programs are ordered by title (falling back to name), and campuses by name. Both use ID to break ties. Inactive records are excluded.

## Program example

```sh
curl -H "Accept: application/json" "https://your-crm-domain.com/api/programs"
```

Example `200 OK` response:

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Web Development",
      "title": "Full Stack Web Development",
      "code": "WEB-001",
      "description": "Learn to build websites.",
      "program_type": "bootcamp",
      "fee": "50000.00",
      "duration_weeks": 12,
      "installments": 3,
      "prerequisite": "Basic computer skills",
      "outline_url": "https://your-crm-domain.com/storage/program-outlines/web.pdf",
      "status": "active",
      "campus_discounts": [
        {
          "campus_id": null,
          "campus_name": null,
          "campus_code": null,
          "discount_percent": "20.00"
        }
      ]
    }
  ]
}
```

Fees and discount percentages are decimal strings with two decimal places. A discount with `campus_id: null` applies to all campuses. Inactive discounts and discounts for inactive campuses are excluded. `campus_discounts` is an empty array when no discounts qualify.

Optional fields can be `null`. `title` falls back to `name` when no title is stored. `outline_url` uses the configured public storage URL and is `null` when no outline path is stored; serving the file requires the existing public disk to be accessible.

## Campus example

```sh
curl -H "Accept: application/json" "https://your-crm-domain.com/api/campuses"
```

Example `200 OK` response:

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Main Campus",
      "title": "Main Campus",
      "slug": "main-campus-fsd",
      "code": "CIFSD01",
      "country": "Pakistan",
      "city": "Faisalabad",
      "city_abbr": "FSD",
      "campus_type": "company",
      "campus_email": "campus@example.com",
      "landline": "0411234567",
      "mobile": "03001234567",
      "address": "123 Example Road",
      "labs_count": 2,
      "status": "active"
    }
  ]
}
```

Internal remarks, royalty rates, discount limits, student records, and user records are not included.

## Empty lists

When there are no active records, the endpoint returns `200 OK`:

```json
{
  "status": "success",
  "data": []
}
```
